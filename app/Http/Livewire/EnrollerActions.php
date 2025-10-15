<?php

namespace App\Http\Livewire;

use App\Http\Controllers\Admin\SaleController;
use App\Models\Category;
use App\Models\Enrollment;
use App\Models\Group;
use App\Models\Meeting;
use App\Models\ReserveMeeting;
use App\Models\Role;
use App\Models\Sale;
use App\Student;
use App\User;
use Livewire\Component;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Livewire\WithPagination;

class EnrollerActions extends Component
{
    use WithPagination;
    protected $paginationTheme = 'bootstrap';

    public $stu_id = 0;
    public $stu_name = null;
    
    public function __construct()
    {
        if (Gate::denies('admin_users_list')) {
            abort(403, 'Unauthorized action.');
        }
    }

    public function delete($id)
    {
        $user = User::find($id);
        $this->stu_id = $id;
        $this->stu_name = $user->full_name;
    }
    
    public function render(Request $request, $is_export_excel = false)
    {
        $query = User::whereHas('student')->whereHas('purchasedFormBundleUnique');

        $salaQuery = Sale::whereNull('refund_at')
            ->whereHas('buyer')
            ->where('type', 'form_fee')
            ->whereNotNull('bundle_id')
            ->whereNotExists(function ($query) {
                $query->selectRaw(1)
                    ->from('sales as s2')
                    ->whereRaw('s2.bundle_id = sales.bundle_id')
                    ->where(function ($query) {
                        $query->where('s2.type', 'bundle')
                            ->orWhere('s2.type', 'installment_payment')
                            ->orWhere('s2.type', 'bridging');
                    })
                    ->whereRaw('s2.buyer_id = sales.buyer_id');
            })
            ->where("payment_method", "!=", 'scholarship')
            ->orderBy('buyer_id', 'desc')
            ->groupBy(['buyer_id', 'bundle_id']);


        $totalStudents = deepClone($query)->count();
        $totalSales = deepClone($salaQuery->get())->count();
        $inactiveStudents = deepClone($query)->where('status', 'inactive')
            ->count();
        $banStudents = deepClone($query)->where('ban', true)
            ->whereNotNull('ban_end_at')
            ->where('ban_end_at', '>', time())
            ->count();

        $totalOrganizationsStudents = User::where('role_name', Role::$user)
            ->whereNotNull('organ_id')
            ->count();
        $userGroups = Group::where('status', 'active')
            ->orderBy('created_at', 'desc')
            ->get();

        $organizations = User::select('id', 'full_name', 'created_at')
            ->where('role_name', Role::$organization)
            ->orderBy('created_at', 'desc')
            ->get();

        $query = (new SaleController())->getSalesFilters($salaQuery, $request);

        if ($is_export_excel) {
            $sales = $query->orderBy('created_at', 'desc')->get();
        } else {
            $sales = $query->orderBy('created_at', 'desc')
                ->paginate(20);
        }

        // $users = $this->addUsersExtraInfo($users);

        if ($is_export_excel) {
            return $sales;
        }

        // $purchasedFormBundle=null;
        // $purchasedUserFormBundle=Sale::where('type', 'form_fee')
        //         ->where('buyer_id', $user->id)
        //         ->first();

        $category = Category::where('parent_id', '!=', null)->get();
        // $requirement=$users[3]->student;
        // dd($requirement);
        $data = [
            'pageTitle' => trans('public.students'),
            'sales' => $sales,
            'category' => $category,
            'totalStudents' => $totalStudents,
            'totalSales' => $totalSales,
            'inactiveStudents' => $inactiveStudents,
            'banStudents' => $banStudents,
            'totalOrganizationsStudents' => $totalOrganizationsStudents,
            'userGroups' => $userGroups,
            'organizations' => $organizations,
        ];

        return view('livewire.enroller-actions',$data);
    }
}
