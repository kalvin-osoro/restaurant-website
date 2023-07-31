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
  <form action="{{url('/uploadchef')}}" method="post" enctype="multipart/form-data" class="row g-3">
            @csrf
            <div class="col-md-6">
                <label for="title" class="form-label">Name</label>
                <input type="text" name="name" id="name" class="form-control" placeholder="Enter name" required>
            </div>

            <div class="col-md-6">
                <label for="speciality" class="form-label">Speciality</label>
                <input type="text" name="speciality" id="speciality" class="form-control" placeholder="Enter the speciality" required>
            </div>

            <div class="col-md-6">
                <label for="image" class="form-label">Image</label>
                <input type="file" name="image" id="image" class="form-control" required>
            </div>

            
            
            <br><br><br><br>

            <div class="col-12">
                <button type="submit" class="btn btn-primary">Submit</button>
            </div>
        </form>

  </div>
 
  
       
       <div class="container">
           

            <table class="table">
                <tr>
                    <th>name</th>
                    <th>speciality</th>
                    <th>image</th>
                    <th>action</th>
                    <th>action2</th>
                </tr>
                @foreach($data as $data)
                <tr>
                <td>{{$data->name}}</td>
                <td>{{$data->speciality}}</td>
                <td><img src="chefImage/{{$data->image}}" alt=""></td>
                <td><a href="{{url('/updatechef',$data->id)}}">Update</a></td>
                <td><a href="{{url('/deletechef',$data->id)}}">Delete</a></td>
                </tr>

                @endforeach
                
            </table>

            
       </div>

  </div>

   @include("admin.adminscript")
  </body>
</html>