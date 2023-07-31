<x-app-layout>
   
</x-app-layout>

<!DOCTYPE html>
<html lang="en">
  <head>

    <base href="/public">

   @include("admin.admincss")
  </head>
  <body>
    
  <div class="container-scroller">

  @include("admin.navbar")

  <div class="container" style=" margin-top: 30px">

            <form action="{{url('/updatefoodchef',$data->id)}}" method="post" enctype="multipart/form-data" class="row g-3">
                @csrf
                <div class="col-md-6">
                    <label for="title" class="form-label">name</label>
                    <input type="text" name="name" id="name" class="form-control" value="{{$data->name}}">
                </div>

                <div class="col-md-6">
                    <label for="price" class="form-label">speciality</label>
                    <input type="text" name="speciality" id="speciality" class="form-control" value="{{$data->speciality}}">
                </div>                

              

                <div class="col-md-6">
                    <label for="Old Image" class="form-label">Old Image</label>
                    <img height="150" width="150" src="/chefImage/{{$data->image}}" alt="">
                </div>
                <br><br><br><br>

                <div class="col-md-6">
                    <label for="image" class="form-label">Image</label>
                    <input type="file" name="image" id="image" class="form-control">
                </div>
                
                <br><br><br><br>

                <div class="col-12">
                    <button type="submit" class="btn btn-primary">Update chef</button>
                </div>
            </form>

        </div>

  </div>

   @include("admin.adminscript")
  </body>
</html>