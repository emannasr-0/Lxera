<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Mixins\Financial\MultiCurrency;
use App\Models\Accounting;
use App\Models\Api\Organization;
use App\Models\Group;
use App\Models\GroupUser;
use App\Models\Sale;
use App\Models\Webinar;
use App\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class DocumentsController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize('admin_documents_list');
        app()->setLocale('ar');
        $documentsQuery = Accounting::query();
        $users = User::whereNull('deleted_at')->get()->keyBy('id');

        $from = $request->input('from');
        $to = $request->input('to');
        $user = $request->input('user');
        $webinar = $request->input('webinar');
        $type = $request->input('type');
        $typeAccount = $request->input('type_account');

        $documentsQuery = fromAndToDateFilter($from, $to, $documentsQuery, 'created_at');

        if (!empty($user)) {
            $documentsQuery->whereIn('user_id', $user);
        }

        $webinarModel = null;
        if (!empty($webinar)) {
            $documentsQuery->where('webinar_id', $webinar);
            $webinarModel = Webinar::find($webinar);
        }


        if (!empty($type) && $type !== 'all') {
            $documentsQuery->where('type', $type);
        }

        if (!empty($typeAccount) && $typeAccount !== 'all') {
            $documentsQuery->where('type_account', $typeAccount);
        }

        $documents = $documentsQuery->orderBy('created_at', 'desc')
            ->orderBy('id', 'desc')
            ->paginate(10);

        $amountTypeOptions = $this->amountTypeOptions();
        $typeOptions = $this->typeOptions();

        $transformedDocuments = $documents->map(function ($doc) {
            $title = null;
            $program = null;

            if ($doc->is_cashback) {
                $title = trans('update.cashback');
            } elseif ($doc->webinar_id) {
                $title = trans('admin/main.item_purchased');
                $program = $doc->webinar ? $doc->webinar->title : null;
            } elseif ($doc->bundle_id) {
                $title = trans('update.bundle_purchased');
                $program = $doc->bundle ? $doc->bundle->title : null;
            } elseif ($doc->product_id) {
                $title = trans('update.product_purchased');
                $program = $doc->product ? $doc->product->title : null;
            } elseif ($doc->meeting_time_id) {
                $title = trans('admin/main.item_purchased') . ' (' . trans('admin/main.meeting') . ')';
            } elseif ($doc->subscribe_id) {
                $title = trans('admin/main.purchased_subscribe');
            } elseif ($doc->promotion_id) {
                $title = trans('admin/main.purchased_promotion');
            } elseif ($doc->registration_package_id) {
                $title = trans('update.purchased_registration_package');
            } elseif ($doc->store_type == Accounting::$storeManual) {
                $title = trans('admin/main.manual_document');
            } else {
                $title = $doc->is_cashback ? $doc->description : trans('admin/main.automatic_document');
            }

            return [
                'id' => $doc->id,
                'title' => $title,
                'program' => $program,
                'user' => $doc->user ? $doc->user->full_name : null,
                'has_tax' => (bool) $doc->tax,
                'has_system' => (bool) $doc->system,
                'amount' => handlePrice($doc->amount),
                'type' => $doc->type == Accounting::$addiction
                    ? trans('admin/main.addiction')
                    : trans('admin/main.deduction'),
                'creator' => $doc->creator_id ? trans('admin/main.admin') : trans('admin/main.automatic'),
                'type_account' => trans('admin/main.' . $doc->type_account),
                'created_at' => Carbon::parse($doc->created_at)->toDateString(),
                'can_print' => auth()->user()->can('admin_documents_print'),
                'print_url' => url('/admin/financial/documents/' . $doc->id . '/print'),
            ];
        });

        return response()->json([
            'pagination' => [
                'total' => $documents->total(),
                'per_page' => $documents->perPage(),
                'current_page' => $documents->currentPage(),
                'last_page' => $documents->lastPage(),
                'next_page_url' => $documents->nextPageUrl(),
                'prev_page_url' => $documents->previousPageUrl(),
            ],
            'data' => $transformedDocuments,
            'amount_types_options' => $amountTypeOptions,
            'types_options' => $typeOptions,
        ]);
    }

    public function store($url_name, Request $request)
    {
        try {
            $organization = Organization::where('url_name', $url_name)->first();
            if (!$organization) {
                return response()->json(['message' => 'Organization not found'], 404);
            }
            $this->authorize('admin_documents_create');

            $this->validate($request, [
                'amount' => 'required|numeric',
                'user_id' => 'required|exists:users,id',
                'type' => 'required|string',
                'currency' => 'required|string',
                'description' => 'nullable|string',
            ]);


            $data = $request->all();
            $user = User::query()->findOrFail($data['user_id']);

            $amount = $data['amount'];

            $multiCurrency = new MultiCurrency();
            $specificCurrency = $multiCurrency->getSpecificCurrency($data['currency']);

            if (!empty($specificCurrency)) {
                $amount = convertPriceToDefaultCurrency($amount, $specificCurrency);
            }

            Accounting::create([
                'creator_id' => auth()->user()->id,
                'amount' => $amount,
                'user_id' => $user->id,
                'type' => $data['type'],
                'description' => $data['description'],
                'type_account' => Accounting::$asset,
                'store_type' => Accounting::$storeManual,
                'created_at' => time(),
            ]);

            $notifyOptions = [
                '[c.title]' => '',
                '[f.d.type]' => $data['type'],
                '[amount]' => handlePrice($amount, true, true, false, $user),
            ];

            sendNotification('new_financial_document', $notifyOptions, $user->id);

            return response()->json([
                'success' => true,
                'message' => 'Document Created Successfully'
            ], 201);
        } catch (\Illuminate\Validation\ValidationException $e) {
            $formattedErrors = [];

            $currentLocale = app()->getLocale();

            foreach ($e->errors() as $field => $messages) {
                $data = $e->validator->getData();
                $rules = [$field => $e->validator->getRules()[$field]];

                app()->setLocale('ar');
                $arValidator = Validator::make($data, $rules);
                $arMessage = $arValidator->errors()->first($field);

                app()->setLocale('en');
                $enValidator = Validator::make($data, $rules);
                $enMessage = $enValidator->errors()->first($field);

                $formattedErrors[$field] = [
                    'ar' => $arMessage,
                    'en' => $enMessage
                ];
            }

            app()->setLocale($currentLocale);

            return response()->json([
                'message' => 'Validation failed',
                'errors' => $formattedErrors
            ], 422);
        }
    }

    public function printer($id)
    {
        $this->authorize('admin_documents_print');

        $document = Accounting::findOrFail($id);

        $data = [
            'document' => $document
        ];

        return view('admin.financial.documents.print', $data);
    }

    public function amountTypeOptions()
    {
        $amountTypeOptions = [];
        $types = ['income', 'asset', 'subscribe', 'promotion', 'registration_package', 'installment_payment'];
        foreach ($types as $type) {
            $amountTypeOptions[] = [
                'label_en' => $type,
                'label_ar' => trans('admin/main.' . $type, [], 'ar'),
            ];
        }
        return $amountTypeOptions;
    }

    public function typeOptions()
    {
        $typeOptions = [];
        $types = ['addiction', 'deduction'];
        foreach ($types as $type) {
            $typeOptions[] = [
                'label_en' => $type,
                'label_ar' => trans('admin/main.' . $type, [], 'ar'),
            ];
        }
        return $typeOptions;
    }
}
