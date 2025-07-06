<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Admin\SaleController;
use App\Http\Controllers\Controller;
use App\Models\Api\Organization;
use App\Models\Discount;
use App\Models\DiscountBundle;
use App\Models\DiscountBundleInstallment;
use App\Models\DiscountCategory;
use App\Models\DiscountCourse;
use App\Models\DiscountGroup;
use App\Models\DiscountUser;
use App\Models\Group;
use App\Models\Sale;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DiscountController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize('admin_discount_codes_list');

        $query = Discount::query();

        $query = $this->filters($query, $request);

        $discounts = $query->orderBy('created_at', 'desc')
            ->with(['discountUsers.user'])
            ->get();

        $data = [
            'success' => true,
            'pageTitle' => trans('admin/main.discount_codes_title'),
            'discounts' => $discounts,
        ];

        return response()->json($data);
    }

    private function filters($query, $request)
    {
        $from = $request->get('from');
        $to = $request->get('to');
        $search = $request->get('search');
        $user_ids = $request->get('user_ids', []);
        $sort = $request->get('sort');


        $query = fromAndToDateFilter($from, $to, $query, 'expired_at');


        if (!empty($user_ids) and count($user_ids)) {
            $discountIds = DiscountUser::whereIn('user_id', $user_ids)->pluck('discount_id');

            $query = $query->whereIn('id', $discountIds);
        }

        if (isset($search)) {
            $query = $query->where('name', 'like', '%' . $search . '%');
        }

        if (!empty($sort)) {
            switch ($sort) {
                case 'percent_asc':
                    $query->orderBy('percent', 'asc');
                    break;
                case 'percent_desc':
                    $query->orderBy('percent', 'desc');
                    break;
                case 'amount_asc':
                    $query->orderBy('amount', 'asc');
                    break;
                case 'amount_desc':
                    $query->orderBy('amount', 'desc');
                    break;
                case 'usable_time_asc':
                    $query->orderBy('count', 'asc');
                    break;
                case 'usable_time_desc':
                    $query->orderBy('count', 'desc');
                    break;
                case 'usable_time_remain_asc':
                    $query->leftJoin('order_items', 'discounts.id', '=', 'order_items.discount_id')
                        ->select('discounts.*', 'order_items.order_id', DB::raw('(discounts.count - count(order_items.order_id)) as remain_count'))
                        ->leftJoin('orders', 'orders.id', '=', 'order_items.order_id')
                        ->where(function ($query) {
                            $query->whereNull('order_id')
                                ->orWhere('orders.status', 'paid');
                        })
                        ->groupBy('order_items.order_id')
                        ->orderBy('remain_count', 'asc');
                    break;
                case 'usable_time_remain_desc':
                    $query->leftJoin('order_items', 'discounts.id', '=', 'order_items.discount_id')
                        ->select('discounts.*', 'order_items.order_id', DB::raw('(discounts.count - count(order_items.order_id)) as remain_count'))
                        ->leftJoin('orders', 'orders.id', '=', 'order_items.order_id')
                        ->where(function ($query) {
                            $query->whereNull('order_id')
                                ->orWhere('orders.status', 'paid');
                        })
                        ->groupBy('order_items.order_id')
                        ->orderBy('remain_count', 'desc');
                    break;
                case 'created_at_asc':
                    $query->orderBy('created_at', 'asc');
                    break;
                case 'created_at_desc':
                    $query->orderBy('created_at', 'desc');
                    break;
                case 'expire_at_asc':
                    $query->orderBy('expired_at', 'asc');
                    break;
                case 'expire_at_desc':
                    $query->orderBy('expired_at', 'desc');
                    break;
            }
        }

        return $query;
    }

    public function store(Request $request)
    {
        $this->authorize('admin_discount_codes_create');

        $this->validate($request, [
            'title' => 'required|string',
            'discount_type' => 'required|in:' . implode(',', Discount::$discountTypes),
            'source' => 'required|in:' . implode(',', Discount::$discountSource),
            'code' => 'required|unique:discounts',
            'user_ids' => 'nullable|array',
            'percent' => 'nullable|numeric',
            'amount' => 'nullable|numeric',
            'count' => 'nullable|numeric',
            'expired_at' => 'required|date',
        ]);

        $data = $request->all();
        $user_id = $data['user_ids'] ?? [];

        $discountType = empty($user_id) ? 'all_users' : 'special_users';
        $expiredAt = convertTimeToUTCzone($data['expired_at'], getTimezone());

        $discount = Discount::create([
            'creator_id' => auth()->id(),
            'title' => $data['title'],
            'discount_type' => $data['discount_type'],
            'source' => $data['source'],
            'code' => $data['code'],
            'percent' => (!empty($data['percent']) && $data['percent'] > 0) ? $data['percent'] : 0,
            'amount' => $data['amount'],
            'max_amount' => $data['max_amount'] ?? null,
            'minimum_order' => $data['minimum_order'] ?? null,
            'count' => (!empty($data['count']) && $data['count'] > 0) ? $data['count'] : 1,
            'user_type' => $discountType,
            'product_type' => $data['product_type'] ?? null,
            'for_first_purchase' => $data['for_first_purchase'] ?? false,
            'status' => 'active',
            'expired_at' => $expiredAt->getTimestamp(),
            'created_at' => time(),
        ]);

        $this->handleRelationItems($discount, $data);

        return response()->json([
            'message' => 'Discount created successfully.',
            'discount' => $discount
        ], 201);
    }

    private function handleRelationItems($discount, $data)
    {
        $usersid = $data['user_ids'] ?? [];
        $coursesIds = $data['webinar_ids'] ?? [];
        $bundlesIds = $data['bundle_ids'] ?? [];
        $bundlesinstallmentIds = $data['bundleinstallment_ids'] ?? [];
        $categoriesIds = $data['category_ids'] ?? [];
        $groupsIds = $data['group_ids'] ?? [];

        if (!empty($usersid)) {
            foreach ($usersid as $user_id) {
                DiscountUser::create([
                    'discount_id' => $discount->id,
                    'user_id' => $user_id,
                    'created_at' => time(),
                ]);
            }
        }

        if (!empty($coursesIds) and count($coursesIds)) {
            foreach ($coursesIds as $coursesId) {
                DiscountCourse::create([
                    'discount_id' => $discount->id,
                    'course_id' => $coursesId,
                    'created_at' => time(),
                ]);
            }
        }

        if (!empty($bundlesIds) and count($bundlesIds)) {
            foreach ($bundlesIds as $bundlesId) {
                DiscountBundle::create([
                    'discount_id' => $discount->id,
                    'bundle_id' => $bundlesId,
                    'created_at' => time(),
                ]);
            }
        }
        if (!empty($bundlesinstallmentIds) and count($bundlesinstallmentIds)) {
            foreach ($bundlesinstallmentIds as $bundlesinstallmentI) {
                DiscountBundleInstallment::create([
                    'discount_id' => $discount->id,
                    'bundle_id' => $bundlesinstallmentI,
                    'created_at' => time(),
                ]);
            }
        }

        if (!empty($categoriesIds) and count($categoriesIds)) {
            foreach ($categoriesIds as $categoryId) {
                DiscountCategory::create([
                    'discount_id' => $discount->id,
                    'category_id' => $categoryId,
                    'created_at' => time(),
                ]);
            }
        }

        if (!empty($groupsIds) and count($groupsIds)) {
            foreach ($groupsIds as $groupsId) {
                DiscountGroup::create([
                    'discount_id' => $discount->id,
                    'group_id' => $groupsId,
                    'created_at' => time(),
                ]);
            }
        }
    }

    public function update($url_name, Request $request, $id)
    {
        $organization = Organization::where('url_name', $url_name)->first();
        if (!$organization) {
            return response()->json(['message' => 'Organization not found'], 404);
        }

        $this->authorize('admin_discount_codes_edit');

        $discount = Discount::findOrFail($id);

        $this->validate($request, [
            'title' => 'sometimes|string',
            'discount_type' => 'sometimes|in:' . implode(',', Discount::$discountTypes),
            'source' => 'sometimes|in:' . implode(',', Discount::$discountSource),
            'code' => 'sometimes|unique:discounts,code,' . $discount->id,
            'user_ids' => 'nullable|array',
            'percent' => 'nullable|numeric',
            'amount' => 'nullable|numeric',
            'count' => 'nullable|numeric',
            'expired_at' => 'sometimes|date',
        ]);

        $data = $request->all();
        $user_id = $data['user_ids'] ?? [];

        $discountType = empty($user_id) ? 'all_users' : 'special_users';
        $expiredAt = convertTimeToUTCzone($data['expired_at'], getTimezone());

        $discount->update([
            'title' => $data['title'],
            'discount_type' => $data['discount_type'],
            'source' => $data['source'],
            'code' => $data['code'],
            'percent' => (!empty($data['percent']) && $data['percent'] > 0) ? $data['percent'] : 0,
            'amount' => $data['amount'],
            'max_amount' => $data['max_amount'] ?? null,
            'minimum_order' => $data['minimum_order'] ?? null,
            'count' => (!empty($data['count']) && $data['count'] > 0) ? $data['count'] : 1,
            'user_type' => $discountType,
            'product_type' => $data['product_type'] ?? null,
            'for_first_purchase' => $data['for_first_purchase'] ?? false,
            'status' => 'active',
            'expired_at' => $expiredAt->getTimestamp(),
        ]);

        // Clean up old relations
        DiscountUser::where('discount_id', $discount->id)->delete();
        DiscountCourse::where('discount_id', $discount->id)->delete();
        DiscountBundle::where('discount_id', $discount->id)->delete();
        DiscountCategory::where('discount_id', $discount->id)->delete();
        DiscountGroup::where('discount_id', $discount->id)->delete();
        DiscountBundleInstallment::where('discount_id', $discount->id)->delete();

        $this->handleRelationItems($discount, $data);

        return response()->json([
            'message' => 'Discount updated successfully.',
            'discount' => $discount
        ]);
    }

    public function destroy($url_name, $id)
    {
        $organization = Organization::where('url_name', $url_name)->first();
        if (!$organization) {
            return response()->json(['message' => 'Organization not found'], 404);
        }
        $this->authorize('admin_discount_codes_delete');

        Discount::find($id)->delete();

        if (!$id) {
            return response()->json(['msg' => 'Cannot found this Discount Code'], 404);
        }

        return response()->json([
            'status' => 'success',
            'msg' => 'Discount code Deleted Successfully'
        ]);
    }

    public function students($url_name, Request $request, Discount $discount)
    {
        $organization = Organization::where('url_name', $url_name)->first();
        if (!$organization) {
            return response()->json(['message' => 'Organization not found'], 404);
        }

        $query = Sale::whereHas('order', function ($query) use ($discount) {
            $query->whereHas('orderItems', function ($query) use ($discount) {
                $query->where('discount_id', $discount->id)->with(['buyer', 'buyer.student']);
            });
        });

        $saleController = new SaleController();
        $query = $saleController->getSalesFilters($query, $request);
        $sales = $query->with(['buyer'])->orderBy('created_at', 'desc')->get();

        foreach ($sales as $sale) {
            $sale = $saleController->makeTitle($sale);
        }

        $data = [
            'sales' => $sales,
            'discount' => $discount
        ];

        return response()->json([
            'status' => true,
            'data' => $data
        ]);
    }
}
