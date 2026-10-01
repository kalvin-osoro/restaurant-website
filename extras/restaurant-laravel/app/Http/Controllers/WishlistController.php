<?php
namespace App\Http\Controllers;
use App\Models\MenuItem;
use Illuminate\Http\Request;
class WishlistController extends Controller { public function index(Request $r){return view('wishlist.index',['items'=>$r->user()->wishlistItems()->get()]);} public function store(Request $r,MenuItem $menuItem){$r->user()->wishlistItems()->syncWithoutDetaching([$menuItem->id]);return back()->with('status','Saved to wishlist.');} public function destroy(Request $r,MenuItem $menuItem){$r->user()->wishlistItems()->detach($menuItem);return back();} }
