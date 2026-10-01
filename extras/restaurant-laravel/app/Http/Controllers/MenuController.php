<?php
namespace App\Http\Controllers;
use App\Models\Category;
use App\Models\MenuItem;
class MenuController extends Controller { public function index(){return view('menu.index',['categories'=>Category::where('is_active',true)->with(['menuItems'=>fn($q)=>$q->where('is_available',true)])->orderBy('sort_order')->get()]);} public function show(MenuItem $menuItem){abort_unless($menuItem->is_available,404);return view('menu.show',compact('menuItem'));} }
