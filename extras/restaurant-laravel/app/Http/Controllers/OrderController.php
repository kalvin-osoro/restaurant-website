<?php
namespace App\Http\Controllers;
use App\Models\Order;
use Illuminate\Http\Request;
class OrderController extends Controller { public function index(Request $r){return view('orders.index',['orders'=>$r->user()->orders()->latest()->paginate()]);} public function show(Request $r,Order $order){abort_unless($order->user_id===$r->user()->id||$r->user()->isAdmin(),403);return view('orders.show',compact('order'));} }
