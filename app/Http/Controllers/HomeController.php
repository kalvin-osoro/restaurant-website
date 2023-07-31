<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use Illuminate\Support\Facades\Auth;

use App\Models\User;

use App\Models\Food;

use App\Models\chef;

class HomeController extends Controller
{
    public function index()
    {
        $data = food::all();
        $data1=chef::all();
        return view("home", compact("data","data1"));
    }

    public function redirects()
    {
        $data = food::all();
        $data1=chef::all();

        $role = Auth::user() -> role;

        if($role == '1')
        {
            return view('admin.adminhome');
        }

        else
        {
            return view("home", compact("data","data1"));
        }
    }
} 
