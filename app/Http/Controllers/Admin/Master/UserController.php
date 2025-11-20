<?php

namespace App\Http\Controllers\Admin\Master;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function index()
    {
        return view('admin.master.users.index');
    }

    /**
     * Display a listing of employees for the leave log.
     *
     * @return \Illuminate\View\View
     */
    public function leaveLogIndex()
    {
        return view('admin.leave-log.index');
    }
}