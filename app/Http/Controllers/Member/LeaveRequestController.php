<?php

namespace App\Http\Controllers\Member;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class LeaveRequestController extends Controller
{
    public function index()
    {
        return view('member.leave_request.index');
    }

    public function create()
    {
        return view('member.leave_request.create');
    }
}
