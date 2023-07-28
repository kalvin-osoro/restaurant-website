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

        <div class="container" style=" margin-top: 30px">

        <form action="{{url('/uploadFood')}}" method="post" enctype="multipart/form-data" class="row g-3">
            @csrf
            <div class="col-md-6">
                <label for="title" class="form-label">Title</label>
                <input type="text" name="title" id="title" class="form-control" placeholder="Enter title" required>
            </div>

            <div class="col-md-6">
                <label for="price" class="form-label">Price</label>
                <input type="text" name="price" id="price" class="form-control" placeholder="Enter price" required>
            </div>

            <div class="col-md-6">
                <label for="image" class="form-label">Image</label>
                <input type="file" name="image" id="image" class="form-control" required>
            </div>

            <div class="col-12">
                <label for="description" class="form-label">Description</label>
                <input type="text" name="description" id="description" class="form-control" placeholder="Enter description" required>
            </div>
            
            <br><br><br><br>

            <div class="col-12">
                <button type="submit" class="btn btn-primary">Submit</button>
            </div>
        </form>

        <div style="margin-top: 10px" >
            <table class="table">
                <tr>
                    <th scop="col">Food name</th>
                    <th scop="col">price</th>
                    <th scop="col">Description</th>
                    <th scop="col">Image</th>
                    <th scop="col">Action</th>
                    <th scop="col">Action2</th>
                </tr>

                @foreach($data as $data)
                <tr>
                    <td>{{$data -> title}}</td>
                    <td>{{$data -> price }}</td>
                    <td>{{$data -> description}}</td>
                    <td><img src="/foodImage/{{$data -> image}}" alt=""></td>
                    <td><a href="{{url('/deleteMenu',$data ->id)}}">Delete</a></td>
                    <td><a href="{{url('/updateMenu',$data ->id)}}">Delete</a></td>
                </tr>
                @endforeach
            </table>
        </div>


        </div>
  </div>

   @include("admin.adminscript")
  </body>
</html>