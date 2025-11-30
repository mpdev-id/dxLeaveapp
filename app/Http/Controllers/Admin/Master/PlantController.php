<?php

namespace App\Http\Controllers\Admin\Master;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class PlantController extends Controller
{
    public function index()
    {
        return view('admin.master.plants.index');
    }
}
