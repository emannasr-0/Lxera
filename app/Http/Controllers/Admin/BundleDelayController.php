<?php

namespace App\Http\Controllers\Admin;

use App\BundleStudent;
use App\Http\Controllers\Controller;
use App\Mixins\Installment\InstallmentPlans;
use App\Models\Bundle;
use App\Models\BundleDelay;
use App\Models\Category;
use App\Models\InstallmentOrder;
use App\Models\Sale;
use App\Models\StudyClass;
use Illuminate\Http\Request;

class BundleDelayController extends Controller
{
    //
    function index(Request $request)
    {
        $query = BundleDelay::whereHas('serviceRequest', function ($query) {
            $query->where('status',  'approved');
        })->orderByDesc('created_at');

        $bundleDelays = $this->filter($request, $query)->paginate(20);
        $lastBatch = StudyClass::latest()->first();

        $categories = Category::whereNull('parent_id')
            ->whereHas('bundles', function ($query) use ($lastBatch) {
                $query->where('batch_id', $lastBatch->id);
            })
            ->orWhereHas('subCategories', function ($query) use ($lastBatch) {
                $query->whereHas('bundles', function ($query) use ($lastBatch) {
                    $query->where('batch_id', $lastBatch->id);
                });
            })
            ->with(
                [
                    'bundles' => function ($query) use ($lastBatch) {
                        $query->where('batch_id', $lastBatch->id);
                    },

                    'subCategories' => function ($query) use ($lastBatch) {
                        $query->whereHas('bundles', function ($query) use ($lastBatch) {
                            $query->where('batch_id', $lastBatch->id);
                        })->with([
                            'bundles' => function ($query) use ($lastBatch) {
                                $query->where('batch_id', $lastBatch->id);
                            },
                        ]);
                    },
                ]
            )->get();



        return view("admin.bundle_delay.index", compact('bundleDelays', 'categories'));
    }

    function approve(Request $request, BundleDelay $bundleDelay)
    {
        $request->validate([
            'from_bundle_id' => 'required|exists:bundles,id',
            'to_bundle_id' => 'required|exists:bundles,id'
        ]);



        if ($request->from_bundle_id == $request->to_bundle_id) {
            return redirect()->back()->with('error', 'From and To Bundle can not be same');
        }
        $fromBundle = Bundle::findOrFail($request->from_bundle_id);
        $toBundle = Bundle::findOrFail($request->to_bundle_id);

        $oldSale = Sale::where(['bundle_id' => $request->from_bundle_id, 'buyer_id' => $bundleDelay->user_id])->whereIn('type', ['bundle', 'installment_payment'])->first();

        if ($oldSale && $oldSale->type == 'installment_payment') {

            $installmentOrder = InstallmentOrder::query()
                ->where(['user_id' => $bundleDelay->user_id, 'bundle_id' => $request->from_bundle_id, 'status' => 'open'])
                ->with(['selectedInstallment', 'selectedInstallment.steps'])->latest()->first();

            $installmentOrder->update([
                'bundle_id' => $toBundle->id,
            ]);
        }

        Sale::where(['buyer_id' => $bundleDelay->user_id, "bundle_id" => $request->from_bundle_id])->update(['bundle_id' => $request->to_bundle_id, 'class_id' => $toBundle->batch_id]);


        $bundleDelay->update(['status' => 'approved']);


        BundleStudent::whereHas('student', function ($query) use ($bundleDelay) {
            $query->where('user_id', $bundleDelay->user_id);
        })->where(['bundle_id' => $request->from_bundle_id])->update(['bundle_id' => $request->to_bundle_id, 'class_id' => $toBundle->batch_id]);

        return redirect()->back()->with('success', 'تم التأجيل بنجاح');
    }
    function filter(Request $request, $query)
    {
        $userName = $request->get('user_name');

        $email = $request->get('email');
        $user_code = $request->get('user_code');

        if (!empty($userName)) {
            $query->when($userName, function ($query) use ($userName) {
                $query->whereHas('user', function ($q) use ($userName) {
                    $q->where('full_name', 'like', "%$userName%");
                });
            });
        }

        if (!empty($email)) {
            $query->when($email, function ($query) use ($email) {
                $query->whereHas('user', function ($q) use ($email) {
                    $q->where('email', 'like', "%$email%");
                });
            });
        }
        if (!empty($user_code)) {
            $query->when($user_code, function ($query) use ($user_code) {
                $query->whereHas('user', function ($q) use ($user_code) {
                    $q->where('user_code', 'like', "%$user_code%");
                });
            });
        }


        return $query;
    }
}
