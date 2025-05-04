<?php

namespace App\Http\Controllers\Admin;

use App\BundleStudent;
use App\Http\Controllers\Controller;
use App\Models\BundleTransform;
use Illuminate\Support\Facades\Mail;
use App\Mail\SendNotifications;
use App\Mixins\Installment\InstallmentPlans;
use App\Models\InstallmentOrder;
use App\Models\Notification;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Role;
use App\Models\Sale;
use App\Models\SelectedInstallmentStep;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class BundleTransformController extends Controller
{
    //
    function index(Request $request)
    {
        $query = BundleTransform::whereHas('serviceRequest', function ($query) {
            $query->where('status',  'approved');
        })->orderByDesc('created_at');

        $transforms = $this->filter($request, $query)->paginate(20);
        return view("admin.bundle_transform.index", compact('transforms'));
    }
    function approve(Request $request, BundleTransform $transform)
    {

        try {
            $transform->status = 'approved';
            $transform->approved_by = Auth::user()->id;


            if ($transform->amount <= 0) {
                return $this->finishTransform($request, $transform);
            }

            if ($transform->type == 'refund') {
                return $this->refund($request, $transform);
            }

            $data['user_id'] = $transform->user_id;
            $data['name'] = $transform->user->full_name;
            $data['receiver'] = $transform->user->email;
            $data['fromEmail'] = env('MAIL_FROM_ADDRESS');
            $data['fromName'] = env('MAIL_FROM_NAME');
            $data['subject'] = 'الرد علي طلب خدمة ' . $transform->serviceRequest->service->title;
            $data['body'] = 'نود اعلامك علي انه تم الموافقة علي طلبك للتحويل من برنامج  ' . $transform->fromBundle->title .  ' إلي برنامج ' . $transform->toBundle->title . '
 متبقي فقط دفع فرق السعر لإتمام التحويل ';

            $this->sendNotification($data);
            $transform->save();
            $financialUsers = User::where(['status' => 'active'])
                ->whereIn('role_id', Role::$financialRoles)->get();
            foreach ($financialUsers as $financialUser) {
                $data['user_id'] = $financialUser->id;
                $data['name'] = $financialUser->full_name;
                $data['receiver'] = $financialUser->email;
                $data['fromEmail'] = env('MAIL_FROM_ADDRESS');
                $data['fromName'] = env('MAIL_FROM_NAME');
                $data['subject'] = 'الرد علي طلب خدمة ' . $transform->serviceRequest->service->title;
                $data['body'] = 'نود اعلامك علي انه تم الموافقة علي طلب التحويل من برنامج  ' . $transform->fromBundle->title .  ' إلي برنامج ' . $transform->toBundle->title . " المقدم من الطالب " . $transform->user->full_name . " من قبل "  . auth()->user()->full_name;
                $this->sendNotification($data);
            }

            $toastData = [
                'title' => " طلب تحويل",
                'msg' => "تم الموافقة علي طلب التحويل بنجاح",
                'status' => 'success'
            ];


            return back()->with(['toast' => $toastData]);
        } catch (\Exception $e) {
            $toastData = [
                'title' => " طلب تحويل",
                'msg' => "حدث خطأ ما يرجي المحاولة مرة اخري",
                'status' => 'error'
            ];


            return back()->with(['toast' => $toastData]);
        }
    }

    function refund(Request $request, bundleTransform $bundleTransform)
    {
        try {
            $user = Auth::user();
            // $order = $this->createOrder($bundleTransform);
            $price = ($bundleTransform->amount);

            $order = Order::create([
                'user_id' => $bundleTransform->user_id,
                'status' => Order::$pending,
                'amount' => $price,
                'tax' => 0,
                'total_discount' => 0,
                'total_amount' =>  $price,
                'product_delivery_fee' => null,
                'created_at' => time(),
            ]);

            $orderItem = OrderItem::create([
                'user_id' => $bundleTransform->user_id,
                'order_id' => $order->id,
                'bundle_id' => $bundleTransform->to_bundle_id,
                'transform_bundle_id' => $bundleTransform->from_bundle_id,
                'amount' => $price,
                'total_amount' => $price,
                'tax_price' => 0,
                'commission' => 0,
                'commission_price' => 0,
                'product_delivery_fee' => 0,
                'discount' => 0,
                'created_at' => time(),
            ]);

            Sale::createSales($orderItem, $order->payment_method, false, true);
            $bundleTransform->status = 'refunded';
            $bundleTransform->approved_by = $user->id;
            $bundleTransform->save();

            $data['user_id'] = $bundleTransform->user_id;
            $data['name'] = $bundleTransform->user->full_name;
            $data['receiver'] = $bundleTransform->user->email;
            $data['fromEmail'] = env('MAIL_FROM_ADDRESS');
            $data['fromName'] = env('MAIL_FROM_NAME');
            $data['subject'] = 'الرد علي طلب خدمة ' . $bundleTransform->serviceRequest->service->title;
            $data['body'] = 'نود اعلامك علي انه تم الموافقة علي طلبك للتحويل من برنامج  ' . $bundleTransform->fromBundle->title .  ' إلي برنامج ' . $bundleTransform->toBundle->title . " واستيرداد مبلغ قدره $bundleTransform->amount ر.س وتم التحويل بنجاح";

            $this->sendNotification($data);

            $financialUsers = User::where(['status' => 'active'])
                ->whereIn('role_id', Role::$financialRoles)->get();
            foreach ($financialUsers as $financialUser) {
                $data['user_id'] = $financialUser->id;
                $data['name'] = $financialUser->full_name;
                $data['receiver'] = $financialUser->email;
                $data['fromEmail'] = env('MAIL_FROM_ADDRESS');
                $data['fromName'] = env('MAIL_FROM_NAME');
                $data['subject'] = 'الرد علي طلب خدمة ' . $bundleTransform->serviceRequest->service->title;
                $data['body'] = 'نود اعلامك علي انه تم الموافقة علي طلب التحويل من برنامج  ' . $bundleTransform->fromBundle->title .  ' إلي برنامج ' . $bundleTransform->toBundle->title . " المقدم من الطالب " . $bundleTransform->user->full_name . " من قبل "  . auth()->user()->full_name .  " واستيرداد مبلغ قدره $bundleTransform->amount ر.س وتم التحويل بنجاح";

                $this->sendNotification($data);
            }

            $toastData = [
                'title' => "اتمام التحويل",
                'msg' => "تم اتمام التحويل واستيرداد المبلغ بنجاح",
                'status' => 'success'
            ];


            return back()->with(['toast' => $toastData]);
        } catch (\Exception $e) {
            $toastData = [
                'title' => " طلب تحويل",
                'msg' => "حدث خطأ ما يرجي المحاولة مرة اخري",
                'status' => 'error'
            ];


            return back()->with(['toast' => $toastData]);
        }
    }

    function finishTransform(Request $request, BundleTransform $transform)
    {

        try {
            $oldSale = Sale::where(['bundle_id' => $transform->from_bundle_id, 'buyer_id' => $transform->user_id])->whereIn('type', ['bundle', 'installment_payment'])->first();

            if ($oldSale && $oldSale->type == 'installment_payment') {

                $installmentOrder = InstallmentOrder::query()
                    ->where(['user_id' => $transform->user_id, 'bundle_id' => $transform->from_bundle_id, 'status' => 'open'])
                    ->with(['selectedInstallment', 'selectedInstallment.steps'])->latest()->first();

                $installmentPlans = new InstallmentPlans($transform->user);

                $newInstallment = $installmentPlans->getPlans(
                    'bundles',
                    $transform->to_bundle_id,
                    $transform->toBundle->type,
                    $transform->toBundle->category_id,
                    $transform->toBundle->teacher_id
                )->last();

                // $oldInstallment = $installmentOrder->selectedInstallment;
                // dd($installmentOrder );
                $installmentOrder->update([
                    'installment_id' => $newInstallment->id,
                    'bundle_id' => $transform->to_bundle_id,
                    'item_price' => $transform->toBundle->price
                ]);
                $installmentOrder->selectedInstallment->update([
                    'installment_id' => $newInstallment->id,
                    'start_date' => $newInstallment->start_date,
                    'end_date' => $newInstallment->end_date,
                    'upfront' => $newInstallment->upfront,
                    'upfront_type' => $newInstallment->upfront_type,
                ]);

                $oldSteps = $installmentOrder->selectedInstallment->steps;
                $newSteps = $newInstallment->steps;

                if (count($oldSteps) <= count($newSteps)) {
                    foreach ($oldSteps as $index => $oldStep) {
                        $newStep =  $newSteps[$index];
                        $oldStep->update([
                            'installment_step_id' => $newStep->id,
                            'amount' => $newStep->amount,
                            'deadline' => $newStep->deadline,

                        ]);
                    }

                    // Add remaining new steps to the database
                    for ($i = count($oldSteps); $i < count($newSteps); $i++) {
                        $newStep = $newSteps[$i];
                        SelectedInstallmentStep::create([
                            'selected_installment_id' =>   $installmentOrder->selectedInstallment->id,
                            'installment_step_id' => $newStep->id,
                            'amount' => $newStep->amount,
                            'deadline' => $newStep->deadline,
                            'amount_type' =>  $newStep->amount_type,
                        ]);
                    }
                } else {
                    foreach ($newSteps as $index => $newStep) {
                        $oldStep =  $oldSteps[$index];
                        $oldStep->update([
                            'installment_step_id' => $newStep->id,
                            'amount' => $newStep->amount,
                            'deadline' => $newStep->deadline,

                        ]);
                    }

                    // Add remaining new steps to the database
                    for ($i = count($newSteps); $i < count($oldSteps); $i++) {
                        $oldStep = $oldSteps[$i];
                        $oldStep->delete();
                    }
                }
            }

            Sale::where(['buyer_id' => $transform->user_id, "bundle_id" => $transform->from_bundle_id])->update(['transform_bundle_id' => $transform->from_bundle_id, 'bundle_id' => $transform->to_bundle_id]);

            BundleStudent::whereHas('student', function ($query) use ($transform) {
                $query->where('user_id', $transform->user_id);
            })->where(['bundle_id' => $transform->to_bundle_id, 'class_id' => null])->delete();

            BundleStudent::whereHas('student', function ($query) use ($transform) {
                $query->where('user_id', $transform->user_id);
            })->where(['bundle_id' => $transform->from_bundle_id])->update(['bundle_id' => $transform->to_bundle_id]);

            $transform->update(['status' => 'paid' , 'approved_by' => Auth::user()->id]);

            $data['user_id'] = $transform->user_id;
            $data['name'] = $transform->user->full_name;
            $data['receiver'] = $transform->user->email;
            $data['fromEmail'] = env('MAIL_FROM_ADDRESS');
            $data['fromName'] = env('MAIL_FROM_NAME');
            $data['subject'] = 'الرد علي طلب خدمة ' . $transform->serviceRequest->service->title;
            $data['body'] = 'نود اعلامك علي انه تم الموافقة علي طلبك للتحويل من برنامج  ' .
                $transform->fromBundle->title .  ' إلي برنامج ' . $transform->toBundle->title . " وتم التحويل بنجاح ";

            $this->sendNotification($data);

            $financialUsers = User::where(['status' => 'active'])
                ->whereIn('role_id', Role::$financialRoles)->get();
            foreach ($financialUsers as $financialUser) {
                $data['user_id'] = $financialUser->id;
                $data['name'] = $financialUser->full_name;
                $data['receiver'] = $financialUser->email;
                $data['fromEmail'] = env('MAIL_FROM_ADDRESS');
                $data['fromName'] = env('MAIL_FROM_NAME');
                $data['subject'] = 'الرد علي طلب خدمة ' . $transform->serviceRequest->service->title;
                $data['body'] = 'نود اعلامك علي انه تم الموافقة علي طلب التحويل من برنامج  ' . $transform->fromBundle->title .  ' إلي برنامج ' . $transform->toBundle->title . " المقدم من الطالب " . $transform->user->full_name . " من قبل "  . auth()->user()->full_name . " وتم التحويل بنجاح ";
                $this->sendNotification($data);
            }

            $toastData = [
                'title' => "اتمام التحويل",
                'msg' => "تم التحويل بنجاح",
                'status' => 'success'
            ];


            return back()->with(['toast' => $toastData]);
        } catch (\Exception $e) {
            $toastData = [
                'title' => " طلب تحويل",
                'msg' => "حدث خطأ في ارسال ايميل للطالب",
                'status' => 'error'
            ];


            return back()->with(['toast' => $toastData]);
        }
    }
    function changeAmount(Request $request, BundleTransform $transform)
    {
        try {


            $request->validate([
                'amount' => 'required|numeric|gte:0|not_in:1,2,3'
            ]);
            $transform->amount = $request->amount;
            $transform->save();
            $toastData = [
                'title' => "تغيير المبلغ",
                'msg' => "تم تغيير المبلغ بنجاح",
                'status' => 'success'
            ];
            return back()->with(['toast' => $toastData]);
        } catch (\Exception $e) {
            $toastData = [
                'title' => "تغيير المبلغ",
                'msg' => $e->getMessage(),
                'status' => 'error'
            ];
            return back()->with(['toast' => $toastData]);
        }
    }

    protected function sendNotification($data)
    {
        // $this->authorize('admin_notifications_send');

        Notification::create([
            'user_id' => !empty($data['user_id']) ? $data['user_id'] : null,
            'sender_id' => auth()->id(),
            'title' => $data['subject'],
            'message' => $data['body'],
            'sender' => Notification::$AdminSender,
            'type' => "single",
            'created_at' => time()
        ]);

        if (!empty($data['user_id']) and env('APP_ENV') == 'production') {
            $user = User::where('id', $data['user_id'])->first();
            if (!empty($user) and !empty($user->email)) {
                Mail::to($user->email)->send(new SendNotifications(['title' => $data['subject'], 'message' => $data['body'], 'name' => $data['name']]));
            }
        }

        return true;
    }



    function filter(Request $request, $query)
    {
        $userName = $request->get('user_name');
        $type = $request->get('type');
        $transformType = $request->get('transform_type');
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
        if (!empty($type)) {
            $query->where('type', 'like', "%$type%");
        }
        if (!empty($transformType)) {
            $query->where('transform_type', 'like', "%$transformType%");
        }

        return $query;
    }
}
