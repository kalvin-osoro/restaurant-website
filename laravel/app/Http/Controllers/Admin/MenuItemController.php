<?php
namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\MenuItem;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
class MenuItemController extends Controller { public function index(){return view('admin.menu.index',['items'=>MenuItem::with('category')->latest()->paginate(),'categories'=>Category::orderBy('name')->get()]);} public function store(Request $r){$data=$this->validateItem($r);if($r->hasFile('image'))$data['image_path']=$r->file('image')->store('menu','public');$data['slug']=Str::slug($data['name']).'-'.Str::lower(Str::random(6));MenuItem::create($data);return back()->with('status','Menu item created.');} public function update(Request $r,MenuItem $menuItem){$data=$this->validateItem($r);if($r->hasFile('image'))$data['image_path']=$r->file('image')->store('menu','public');$menuItem->update($data);return back()->with('status','Menu item updated.');} public function destroy(MenuItem $menuItem){$menuItem->delete();return back()->with('status','Menu item deleted.');} private function validateItem(Request $r):array{return $r->validate(['category_id'=>'required|exists:categories,id','name'=>'required|string|max:120','description'=>'nullable|string|max:2000','price'=>'required|decimal:0,2|min:0','image'=>'nullable|image|max:4096','is_available'=>'boolean']);} }
