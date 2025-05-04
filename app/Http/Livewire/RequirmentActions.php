<?php

namespace App\Http\Livewire;

use App\BundleStudent;
use App\Student;
use App\StudentRequirement;
use Livewire\Component;
use Illuminate\Http\Request;
use Livewire\WithPagination;

class RequirmentActions extends Component
{
    use WithPagination;
    protected $paginationTheme = 'bootstrap';
    public $requirement_id=0;
    public $stu_name = null;


    public function reject(StudentRequirement $id){
        $this->requirement_id=$id;
        
    }

    public function approve(StudentRequirement $id){
        $this->requirement_id=$id;
        $this->stu_name = $id->bundleStudent->student->ar_name;
    }


    public function filters($query, $request)
    {

        $user_code = $request->get('user_code');
        $ar_name = $request->get('ar_name');
        $email = $request->get('email');
        $mobile = $request->get('mobile');

        if (!empty($ar_name)) {
            $query->whereHas('bundleStudent.student', function ($q) use ($ar_name) {
                $q->where('ar_name', 'like', "%$ar_name%");
                $q->orWhere('en_name', 'like', "%$ar_name%");
            });
        }
        if (!empty($user_code)) {
            $query->whereHas('bundleStudent.student.registeredUser', function ($q) use ($user_code) {
                $q->where('user_code', 'like', "%$user_code%");
            });
        }

        if (!empty($email)) {
            $query->whereHas('bundleStudent.student.registeredUser', function ($q) use ($email) {
                $q->where('email', 'like', "%$email%");
            });
        }
        if (!empty($mobile)) {
            $query->whereHas('bundleStudent.student.registeredUser', function ($q) use ($mobile) {
                $q->where('mobile', 'like', "%$mobile%");
            });
        }
        return $query;
    }

    public function render(Request $request)
    {
        $query=StudentRequirement::orderByDesc('created_at');
        $query = $this->filters($query, $request);
        $requirements = $query->paginate(20);

        return view('livewire.requirment-actions',['requirements' => $requirements]);
    }
}
