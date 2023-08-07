<x-app-layout>
   
</x-app-layout>

<!DOCTYPE html>
<html lang="en">
  <head>
   @include("admin.admincss")
  </head>
  <body>
    
  <div class="container-scroller">

  @include("admin.navbar")

  <div class="container">
  

  <div class="container" style="margin-bottom: 50px;">

        <form action="{{url('/search')}}" method="get" class="form-control">

        @csrf

            <input type="text" name="search" style="color: blue">
            <input type="submit" value="search" class="btn btn-success">

        </form>
  </div>

  <div class="container">

  <table class="table">
    <tr>
        <td>name</td>
        <td>Phone</td>
        <td>Address</td>
        <td>Food name</td>
        <td>image</td>
        <td>Price</td>
        <td>Quantity</td>
        <td>Total Price</td>
    </tr>

    @foreach($data as $item)

    <tr>
        <td>{{$item->name}}</td>
        <td>{{$item->phone}}</td>
        <td>{{$item->address}}</td>
        <td>{{$item->foodname}}</td>
        <td><img src="foodImage/{{$item->image}}" alt=""></td>
        <td>{{$item->price}}$</td>
        <td>{{$item->quantity}}</td>
        <td>{{$item->price* $item->quantity}}$</td>        
    </tr>
   

    @endforeach
  </table>

  </div>

  </div>
  

  </div>

   @include("admin.adminscript")
  </body>
</html>