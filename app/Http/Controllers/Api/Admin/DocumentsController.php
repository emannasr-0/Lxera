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
use Illuminate\Http\Request;

class DocumentsController extends Controller
{

    public function index(Request $request)
    {
        $this->authorize('admin_documents_list');

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
            $documentsQuery->whereIn('webinar_id', [$webinar]);
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
            ->get();

        $transformedDocuments = $documents->map(function ($doc) {
            $title = null;
            $link = null;

            if ($doc->is_cashback) {
                $title = trans('update.cashback');
            } elseif ($doc->webinar_id) {
                $title = trans('admin/main.item_purchased');
                $link = $doc->webinar ? $doc->webinar->getUrl() : null;
            } elseif ($doc->bundle_id) {
                $title = trans('update.bundle_purchased');
                $link = $doc->bundle ? $doc->bundle->getUrl() : null;
            } elseif ($doc->product_id) {
                $title = trans('update.product_purchased');
                $link = $doc->product ? $doc->product->getUrl() : null;
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
                'link' => $link,
                'user' => $doc->user ? $doc->user->full_name : null,
                'has_tax' => (bool) $doc->tax,
                'has_system' => (bool) $doc->system,
                'amount' => handlePrice($doc->amount),
                'type' => $doc->type == Accounting::$addiction
                    ? trans('admin/main.addiction')
                    : trans('admin/main.deduction'),
                'creator' => $doc->creator_id ? trans('admin/main.admin') : trans('admin/main.automatic'),
                'type_account' => trans('admin/main.' . $doc->type_account),
                'created_at' => dateTimeFormat($doc->created_at, 'j F Y H:i'),
                'can_print' => auth()->user()->can('admin_documents_print'),
                'print_url' => url('/admin/financial/documents/' . $doc->id . '/print'),
            ];
        });

        return response()->json([
            'data' => $transformedDocuments,
        ]);
    }

    public function store($url_name, Request $request)
    {
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
}
