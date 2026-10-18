<?php
namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
class UserController extends Controller { public function index(){return view('admin.users.index',['users'=>User::latest()->paginate()]);} public function store(Request $r){$data=$r->validate(['name'=>'required|string|max:120','email'=>'required|email|unique:users','password'=>'required|confirmed|min:12','role'=>'required|in:customer,admin']);User::create($data);return back()->with('status','User created.');} public function update(Request $r,User $user){$data=$r->validate(['role'=>'required|in:customer,admin']);if($user->id===$r->user()->id&&$data['role']!=='admin')return back()->withErrors(['role'=>'You cannot remove your own admin access.']);$user->update($data);return back()->with('status','User role updated.');} }
