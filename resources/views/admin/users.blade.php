<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>
<body>

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

  <!-- user-table -->
  <div>
    <table class="table">
        <tr>
            <th scope="col">name</th>
            <th scope="col">email</th>
            <th scope="col">action</th>
            <th scope="col">role</th>
        </tr>

        @foreach($data as $data)
        <tr>        
            <td>{{$data->name}}</td>
            <td>{{$data->email}}</td>
            <td>{{$data->role}}</td>

            @if($data->role=="0")
            <td><a href="{{url('/deleteUser', $data->id)}}">delete</a></td>
            @else
            <td><a href="">Not Allowed</a></td>

            @endif
        </tr>
        @endforeach

    </table>
  </div>
  <!-- user-table end -->

  </div>

   @include("admin.adminscript")
  </body>
</html>


</body>
</html>