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


        </div>
  </div>

   @include("admin.adminscript")
  </body>
</html>