<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\User;

use App\Models\Food;

use App\Models\Reservation;

class AdminController extends Controller
{
    public function user()
    {
        $data=user::all();
        return view("admin.users",compact("data"));
    }

    public function deleteUser($id)
    {
        $data=user::find($id);
        $data->delete();
        return redirect()->back();
    }

    public function deletemenu($id)
    {
        $data=food::find($id);
        $data ->delete();
        return redirect()->back();
    }

    public function foodMenu()
    {
        $data=food::all();
        return view("admin.foodMenu",compact("data"));
    }

    public function updateMenu($id)
    {
       $data=food::find($id);
       return view("admin.updateMenu",compact("data"));
    }

    public function update(Request $request, $id)
    {
        $data=food::find($id);

        $image = $request -> image;

        $imageName = time().'.'.$image -> getClientOriginalExtension();
        $request -> image -> move('foodImage', $imageName);

        $data -> image = $imageName;
        $data -> title = $request -> title;
        $data -> price = $request -> price;
        $data -> description = $request -> description;
        $data -> save();

        return redirect() -> back();
    }

    public function upload(Request $request)
    {
      $data = new food;  

      $image = $request -> image;

      $imageName = time().'.'.$image -> getClientOriginalExtension();
      $request -> image -> move('foodImage', $imageName);

      $data -> image = $imageName;
      $data -> title = $request -> title;
      $data -> price = $request -> price;
      $data -> description = $request -> description;
      $data -> save();

      return redirect() -> back();
    }

    public function reservation(Request $request)
    {
      $data = new reservation;  

     
      $data -> name = $request -> name;
      $data -> email = $request -> email;
      $data -> phone = $request -> phone;
      $data -> guest = $request -> guest;
      $data -> date = $request -> date;
      $data -> time = $request -> time;
      $data -> message = $request -> message;      
      $data -> save();

      return redirect() -> back();
    }
}
