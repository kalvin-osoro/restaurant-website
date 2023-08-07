<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use Illuminate\Support\Facades\Auth;

use App\Models\User;

use App\Models\Food;

use App\Models\Chef;

use App\Models\Cart;

use App\Models\Order;

class HomeController extends Controller
{
    public function index()
    {

        if(Auth::id())
        {
            return redirect('redirects');
        }
        else

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
            $user_id =Auth::id();
            $count=cart::where('user_id', $user_id)->count();


            return view("home", compact("data","data1","count"));
        }
    }

    public function addCart(Request $request, $id)
    {
       if(Auth::id())
       {
        $user_id=Auth::id();

        $foodID=$id;

        $quantity = $request->quantity;

        $cart = new cart;

        $cart->user_id=$user_id;

        $cart->food_id=$foodID;

        $cart->quantity=$quantity;

        $cart->save();

        return redirect()->back();
       } 
       else
       {
        return redirect('/login');
       }
    
       
    }

    public function showCart(Request $request, $id)
    {
        $count=cart::where('user_id',$id)->count();

        if(Auth::id()==$id)
        {

        

            $data1=cart::select('*')->where('user_id', '=', $id)->get();

            $data=cart::where('user_id',$id)->join('food','carts.food_id','=','food.id')->get();

             return view('showcart',compact("count","data", "data1")); 

        }
        else
        {
            return redirect()->back(); 
        }
    }

    public function remove($id)
    {
        $data=cart::find($id);

        $data->delete();

        return redirect()->back();
    }

    public function orderConfirm(Request $request)
    {
        foreach($request->foodname as $key =>$foodname)
        {
            $data= new order;

            $data->foodname=$foodname;

            $data->price=$request->price[$key];

            $data->quantity=$request->quantity[$key];

            $data->image=$request->image[$key];

            $data->name=$request->name;

            $data->phone=$request->phone;

            $data->address=$request->address;

            $data->save();
        }

        return redirect()->back();

    }

} 
