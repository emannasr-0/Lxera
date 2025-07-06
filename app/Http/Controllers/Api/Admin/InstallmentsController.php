<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Api\Admin\traits\InstallmentOrdersTrait;
use App\Http\Controllers\Api\Admin\traits\InstallmentOverdueTrait;
use App\Http\Controllers\Api\Admin\traits\InstallmentPurchasesTrait;
use App\Http\Controllers\Api\Admin\traits\InstallmentSettingsTrait;
use App\Http\Controllers\Admin\traits\InstallmentVerificationRequestsTrait;
use App\Http\Controllers\Controller;
use App\Models\Api\Organization;
use App\Models\Bundle;
use App\Models\Category;
use App\Models\Group;
use App\Models\Installment;
use App\Models\InstallmentOrder;
use App\Models\InstallmentSpecificationItem;
use App\Models\InstallmentStep;
use App\Models\InstallmentUserGroup;
use App\Models\RegistrationPackage;
use App\Models\SelectedInstallment;
use App\Models\SelectedInstallmentStep;
use App\Models\Subscribe;
use App\Models\Translation\InstallmentStepTranslation;
use App\Models\Translation\InstallmentTranslation;
use Illuminate\Http\Request;
use App\Models\StudyClass;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class InstallmentsController extends Controller
{
    use InstallmentSettingsTrait;
    use InstallmentPurchasesTrait;
    use InstallmentOverdueTrait;
    use InstallmentVerificationRequestsTrait;
    use InstallmentOrdersTrait;

    public function index()
    {
        $this->authorize('admin_installments_list');

        $installments = Installment::query()
            ->orderBy('created_at', 'desc')
            ->withCount([
                'steps'
            ])
            ->get();

        foreach ($installments as $installment) {
            $installment->sales_count = InstallmentOrder::query()
                ->where('installment_id', $installment->id)
                ->whereIn('status', ['open', 'pending_verification'])
                ->count();
        }

        $data = [
            'installments' => $installments,
        ];

        return response()->json($data);
    }

    public function store(Request $request)
    {
        $this->authorize('admin_installments_create');

        $data = $request->all();

        $validator = Validator::make($data, [
            'title' => 'required',
            'main_title' => 'required',
            'description' => 'required',
            'capacity' => 'required|integer',
            'target' => 'required',
            'target_type' => 'required',
            'upfront' => 'nullable|numeric',
            'duration_limit' => 'required|integer',
            'deadline_type' => 'required',
            'locale' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        $startDate = !empty($data['start_date'])
            ? convertTimeToUTCzone($data['start_date'], getTimezone())->getTimestamp()
            : null;

        $endDate = !empty($data['end_date'])
            ? convertTimeToUTCzone($data['end_date'], getTimezone())->getTimestamp()
            : null;

        $lastBatch = StudyClass::latest()->first();

        $installment = Installment::create([
            'target_type' => $data['target_type'],
            'target' => $data['target'],
            'capacity' => $data['capacity'] ?? null,
            'start_date' => $startDate,
            'end_date' => $endDate,
            'verification' => !empty($data['verification']) && $data['verification'] === 'on',
            'request_uploads' => !empty($data['request_uploads']) && $data['request_uploads'] === 'on',
            'bypass_verification_for_verified_users' => !empty($data['bypass_verification_for_verified_users']) && $data['bypass_verification_for_verified_users'] === 'on',
            'upfront' => $data['upfront'] ?? null,
            'upfront_type' => !empty($data['upfront']) ? $data['upfront_type'] : null,
            'enable' => !empty($data['enable']) && $data['enable'] === 'on',
            'batch_id' => $data['batch_id'] ?? ($lastBatch->id ?? null),
            'duration_limit' => $data['duration_limit'],
            'deadline_type' => $data['deadline_type'],
            'created_at' => time(),
        ]);

        if ($installment) {
            $this->storeExtraData($installment, $data);

            return response()->json([
                'message' => trans('update.new_installments_were_successfully_created'),
                'status' => 'success',
                'data' => $installment,
            ], 201);
        }

        return response()->json([
            'message' => 'Internal server error',
            'status' => 'error',
        ], 500);
    }

    private function storeExtraData(Installment $installment, array $data)
    {
        $locale = mb_strtolower($data['locale'] ?? app()->getLocale());

        InstallmentTranslation::updateOrCreate([
            'installment_id' => $installment->id,
            'locale' => $locale,
        ], [
            'title' => $data['title'] ? $installment->title : null,
            'main_title' => $data['main_title'] ? $installment->main_title : null,
            'description' => $data['description'] ? $installment->description : null,
            'banner' => $data['banner'] ?? null,
            'options' => !empty($data['installment_options'])
                ? implode(Installment::$optionsExplodeKey, array_filter($data['installment_options']))
                : null,
            'verification_description' => $data['verification_description'] ?? null,
            'verification_banner' => $data['verification_banner'] ?? null,
            'verification_video' => $data['verification_video'] ?? null,
        ]);

        InstallmentSpecificationItem::where('installment_id', $installment->id)->delete();

        $specItems = [
            'category_ids'            => 'category_id',
            'instructor_ids'          => 'instructor_id',
            'seller_ids'              => 'seller_id',
            'webinar_ids'             => 'webinar_id',
            'product_ids'             => 'product_id',
            'bundle_ids'              => 'bundle_id',
            'subscribe_ids'           => 'subscribe_id',
            'registration_package_ids' => 'registration_package_id',
        ];

        foreach ($specItems as $key => $col) {
            if (!empty($data[$key]) && $this->checkStoreSpecificationItems($key, $installment->target, $installment->target_type)) {
                $insert = [];

                foreach ($data[$key] as $item) {
                    $insert[] = [
                        'installment_id' => $installment->id,
                        $col             => $item,
                    ];
                }

                if ($insert) {
                    InstallmentSpecificationItem::insert($insert);
                }
            }
        }

        $ignoreIds = [];
        if (!empty($data['steps'])) {
            $order = 0;

            foreach ($data['steps'] as $stepId => $stepData) {
                if ($stepId === 'record' || empty($stepData)) {
                    continue;
                }

                $step = InstallmentStep::where('id', $stepId)
                    ->where('installment_id', $installment->id)
                    ->first();

                $deadline = null;
                if (!empty($stepData['deadline'])) {
                    $deadline = $stepData['deadline'];
                    if ($installment->deadline_type === 'date') {
                        $deadline = convertTimeToUTCzone($stepData['deadline'], getTimezone())->getTimestamp();
                    }
                }

                if ($step) {
                    $step->update([
                        'deadline'     => $deadline,
                        'amount'       => $stepData['amount'] ?? null,
                        'amount_type'  => $stepData['amount_type'] ?? null,
                        'order'        => $order,
                    ]);
                } else {
                    $step = InstallmentStep::create([
                        'installment_id' => $installment->id,
                        'deadline'       => $deadline,
                        'amount'         => $stepData['amount'] ?? null,
                        'amount_type'    => $stepData['amount_type'] ?? null,
                        'order'          => $order,
                    ]);
                }

                if ($step) {
                    $ignoreIds[] = $step->id;

                    InstallmentStepTranslation::updateOrCreate([
                        'installment_step_id' => $step->id,
                        'locale'              => $locale,
                    ], [
                        'title' => $stepData['title'],
                    ]);

                    SelectedInstallmentStep::where('installment_step_id', $step->id)
                        ->update(['deadline' => $step->deadline]);
                }

                $order++;
            }
        }

        InstallmentStep::where('installment_id', $installment->id)
            ->whereNotIn('id', $ignoreIds)
            ->delete();

        InstallmentUserGroup::where('installment_id', $installment->id)->delete();

        if (!empty($data['group_ids'])) {
            $groups = array_filter($data['group_ids']);
            $insert = array_map(fn($g) => [
                'installment_id' => $installment->id,
                'group_id'       => $g,
            ], $groups);

            InstallmentUserGroup::insert($insert);
        }
    }

    private function checkStoreSpecificationItems(string $item, $target, $type): bool
    {
        $mapping = [
            'category_ids'             => 'specific_categories',
            'instructor_ids'           => 'specific_instructors',
            'seller_ids'               => 'specific_sellers',
            'webinar_ids'              => 'specific_courses',
            'product_ids'              => 'specific_products',
            'bundle_ids'               => 'specific_bundles',
            'subscribe_ids'            => 'specific_packages',
            'registration_package_ids' => 'specific_packages',
        ];

        if (($mapping[$item] ?? null) === $target) {
            return in_array($item, ['subscribe_ids', 'registration_package_ids'])
                ? $type === ($item === 'subscribe_ids' ? 'subscription_packages' : 'registration_packages')
                : true;
        }

        return false;
    }

    public function update($url_name, Request $request, $id)
    {
        try {
            $organization = Organization::where('url_name', $url_name)->first();
            if (!$organization) {
                return response()->json(['message' => 'Organization not found'], 404);
            }

            $this->authorize('admin_installments_edit');

            $data = $request->all();
            $this->validate($request, [
                'title' => 'sometimes|string',
                'main_title' => 'sometimes|string',
                'description' => 'sometimes|string',
                'target' => 'sometimes|string',
                'target_type' => 'sometimes|in:all,courses,store_products,bundles,meetings,registration_packages,subscription_packages',
                'upfront' => 'nullable|numeric',
                'duration_limit' => 'sometimes|integer',
                'deadline_type' => 'sometimes',
                'locale' => 'nullable|string',
            ]);

            $installment = Installment::findOrFail($id);

            $startDate = !empty($data['start_date'])
                ? convertTimeToUTCzone($data['start_date'], getTimezone())->getTimestamp()
                : $installment->start_date;

            $endDate = !empty($data['end_date'])
                ? convertTimeToUTCzone($data['end_date'], getTimezone())->getTimestamp()
                : $installment->end_date;

            $installment->update([
                'target_type' => $data['target_type'] ?? $installment->target_type,
                'target' => $data['target'] ?? $installment->target,
                'capacity' => $data['capacity'] ?? $installment->capacity,
                'start_date' => $startDate,
                'end_date' => $endDate,
                'verification' => isset($data['verification']) ? $data['verification'] === 'on' : $installment->verification,
                'request_uploads' => isset($data['request_uploads']) ? $data['request_uploads'] === 'on' : $installment->request_uploads,
                'bypass_verification_for_verified_users' => isset($data['bypass_verification_for_verified_users']) ? $data['bypass_verification_for_verified_users'] === 'on' : $installment->bypass_verification_for_verified_users,
                'upfront' => $data['upfront'] ?? $installment->upfront,
                'upfront_type' => isset($data['upfront']) ? ($data['upfront_type'] ?? null) : $installment->upfront_type,
                'enable' => isset($data['enable']) ? $data['enable'] === 'on' : $installment->enable,
                'batch_id' => $data['batch_id'] ?? $installment->batch_id,
                'duration_limit' => $data['duration_limit'] ?? $installment->duration_limit,
                'deadline_type' => $data['deadline_type'] ?? $installment->deadline_type,
                'updated_at' => time(),
            ]);

            if (!empty($data['title']) || !empty($data['main_title']) || !empty($data['description'])) {
                $locale = $data['locale'] ?? app()->getLocale();

                $translation = $installment->translations()->where('locale', $locale)->first();

                if ($translation) {
                    $translation->update([
                        'title' => $data['title'] ?? $translation->title,
                        'main_title' => $data['main_title'] ?? $translation->main_title,
                        'description' => $data['description'] ?? $translation->description,
                    ]);
                }
            }


            return response()->json([
                'message' => 'Installment Updated Successfully',
                'status' => 'success',
                'data' => $installment,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Internal server error',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function delete($url_name, $id)
    {
        $organization = Organization::where('url_name', $url_name)->first();
        if (!$organization) {
            return response()->json(['message' => 'Organization not found'], 404);
        }

        $this->authorize('admin_installments_delete');

        $installment = Installment::query()->findOrFail($id);

        $installment->delete();

        $toastData = [
            'msg' => 'Installment Deleted Successfully',
            'status' => 'success'
        ];

        return response()->json($toastData);
    }
}
