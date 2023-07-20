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

  <div>

  </div>

  <div class="container" style=" margin-top: 30px">

  <form action="" class="row g-3">
    <div class="form-group">
        <label for="">title</label>
        <input type="text" name="title" placeholder="enter title" Required>
    </div>

    <div class="form-group">
        <label for="">price</label>
        <input type="text" name="price" placeholder="enter price" Required>
    </div>

    <div class="form-group">
        <label for="">image</label>
        <input type="file" name="image"  Required>
    </div>

    <div class="form-group">
        <label for="">description</label>
        <input type="text" name="description" placeholder="enter description" Required>
    </div>

    <div class="form-group">
        <input type="submit" class="btn btn-default">
    </div>
    
  </form>
  </div>

  

  </div>

   @include("admin.adminscript")
  </body>
</html>