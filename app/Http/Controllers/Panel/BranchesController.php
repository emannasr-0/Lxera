<?php

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class BranchesController extends Controller
{
    public function index(){
        return view('web.default.panel.branches.index');
    }

    public function create(){
        return view('web.default.panel.branches.create');
    }
}
