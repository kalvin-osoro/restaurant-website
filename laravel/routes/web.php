<?php
use App\Http\Controllers\Admin\MenuItemController as AdminMenuItemController;
use App\Http\Controllers\Admin\UserController as AdminUserController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\MenuController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\WishlistController;
use App\Models\MenuItem;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Route;

Route::get('/', fn()=>view('home'))->name('home');
Route::get('/menu',[MenuController::class,'index'])->name('menu.index');
Route::get('/menu/{menuItem:slug}',[MenuController::class,'show'])->name('menu.show');
Route::get('/cart',[CartController::class,'index'])->name('cart.index');
Route::post('/cart/{menuItem}',[CartController::class,'store'])->name('cart.store');
Route::patch('/cart/{menuItem}',[CartController::class,'update'])->name('cart.update');
Route::middleware('guest')->group(function(){
 Route::get('/register',fn()=>view('auth.register'))->name('register');
 Route::post('/register',function(Request $r){$data=$r->validate(['name'=>'required|string|max:120','email'=>'required|email|unique:users','password'=>'required|confirmed|min:12']);$user=User::create($data);Auth::login($user);return redirect()->route('home');});
 Route::get('/login',fn()=>view('auth.login'))->name('login');
 Route::post('/login',function(Request $r){$credentials=$r->validate(['email'=>'required|email','password'=>'required']);if(!Auth::attempt($credentials,$r->boolean('remember')))return back()->withErrors(['email'=>'Invalid credentials.'])->onlyInput('email');$r->session()->regenerate();return redirect()->intended(route('home'));});
});
Route::post('/logout',function(Request $r){Auth::logout();$r->session()->invalidate();$r->session()->regenerateToken();return redirect()->route('home');})->middleware('auth')->name('logout');
Route::middleware('auth')->group(function(){Route::get('/wishlist',[WishlistController::class,'index'])->name('wishlist.index');Route::post('/wishlist/{menuItem}',[WishlistController::class,'store'])->name('wishlist.store');Route::delete('/wishlist/{menuItem}',[WishlistController::class,'destroy'])->name('wishlist.destroy');Route::get('/checkout',[CheckoutController::class,'create'])->name('checkout.create');Route::post('/checkout',[CheckoutController::class,'store'])->name('checkout.store');Route::get('/orders',[OrderController::class,'index'])->name('orders.index');Route::get('/orders/{order}',[OrderController::class,'show'])->name('orders.show');});
Route::prefix('admin')->middleware(['auth','admin'])->group(function(){Route::get('/menu',[AdminMenuItemController::class,'index'])->name('admin.menu.index');Route::post('/menu',[AdminMenuItemController::class,'store'])->name('admin.menu.store');Route::patch('/menu/{menuItem}',[AdminMenuItemController::class,'update'])->name('admin.menu.update');Route::delete('/menu/{menuItem}',[AdminMenuItemController::class,'destroy'])->name('admin.menu.destroy');Route::get('/users',[AdminUserController::class,'index'])->name('admin.users.index');Route::post('/users',[AdminUserController::class,'store'])->name('admin.users.store');Route::patch('/users/{user}',[AdminUserController::class,'update'])->name('admin.users.update');});
