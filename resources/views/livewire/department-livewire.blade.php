<div>
  <div class="row">
    <div class="col-md-12 mb-30">
      <div class="card card-statistics h-100">
        <div class="card-body">
          <div class="card-body">
            <h5 class="card-title">Add New Department</h5>
            <form>
              <div class="mb-3">
                <label class="form-label" for="exampleInputEmail1">Department</label>
                <input wire:model="department" type="text" class="form-control" aria-describedby="emailHelp"
                  placeholder="IT">
                @error('department') <span class="text-danger">{{ $message }}</span> @enderror
              </div>
              <div class="mb-3">
                <label class="form-label" for="exampleInputEmail1">Department Manager</label>
                <select wire:model="selectedManager" class="form-control p-2" id="inlineFormSelectPref">
                  <option selected>Choose...</option>
                  @foreach ($managerSelect as $manager)
                  @if ($manager->role_id == 3)
                  <option value="{{ $manager->user_id }}">{{ $manager->users->name }}</option>
                  @endif
                  @endforeach
                </select>
                @error('selectedManager') <span class="text-danger">{{ $message }}</span> @enderror
              </div>

              @if($editMode)
              <button wire:click.prevent="updateDepartment" class="btn btn-primary">Update</button>
              <button wire:click.prevent="cancelUpdate" class="btn btn-secondary">Cancel</button>
              @else
              <button wire:click.prevent="addDepartment" class="btn btn-primary">Add</button>
              @endif
            </form>
          </div>
        </div>
      </div>
    </div>
  </div>

  {{-- ==========================================================Departments
  Detiles===================================================== --}}

  <div class="row">
    @foreach($departments as $department)
    {{-- @if (Auth::User()->id == $department->manager_id) --}}
    <div class="col-sm-6 col-lg-6 col-xl-2 mb-30">
      <div class="card card-statistics h-100">
        <div class="card-body">
          {{-- <a href="#" class="text-dark float-end" data-bs-toggle="tooltip" data-bs-placement="left" title=""
            data-bs-original-title="View project"><i class="fa fa-eye"></i></a>
          <span>Envato market</span> --}}
          <h5 class="mt-15 mb-15"><b>{{ $department->dep_name }}</b></h5>
          <p> Created By: {{ $department->user->name }}</p>
          <p> Manager:
            @php
            $managers = App\Models\User::where('id',$department->manager_id)->get();
            foreach ($managers as $manager) {
            echo $manager->name;
            }
            @endphp
          </p>

          <div class="row">
            <div class="col-6 col-sm-6 mt-30">
              <b>Folders</b>
              <h4 class="text-success mt-10">{{$department->folders->count()}}</h4>
              
            </div>
            <div class="col-6 col-sm-6 mt-30">
              <b>Files</b>
              <h4 class="text-danger mt-10">{{$department->files->count()}}</h4>
            </div>
            {{-- <div class="col-4 col-sm-4 mt-30">
              <b>Employees</b>
              <h4 class="text-warning mt-10">80</h4>
            </div> --}}
          </div>
          <div class="row">
            <div class="col-12 col-sm-12 mt-30">
              <div class="card-body">
                <a wire:click.prevent="editDepartment({{ $department->id }})" class="button button-border x-small"
                  href="#">Edit </a>
                  <button class="button button-border x-small"  title="Delete"
                  data-toggle="modal" data-target="#deleteModal">Delete</button>

             <!-- Delete Modal -->
             <div class="modal fade" id="deleteModal" tabindex="-1" role="dialog"
               aria-labelledby="deleteModalLabel" aria-hidden="true">
               <div class="modal-dialog" role="document">
                 <div class="modal-content">
                   <div class="modal-header">
                     <h5 class="modal-title" id="deleteModalLabel">Delete User</h5>
                     <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                       <span aria-hidden="true">&times;</span>
                     </button>
                   </div>
                   <div class="modal-body">
                     Are you sure you want to delete this File?
                   </div>
                   <div class="modal-footer">
                     <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                     <button wire:click="DepartmentDelete({{ $department->id }})" type="button"
                       class="btn btn-danger">Delete</button>
                   </div>
                 </div>
               </div>
             </div>
              </div>
            </div>
          </div>

        </div>
      </div>
    </div>
    
    {{-- @endif --}}
    
    
    @endforeach
    
  </div>
  <div class="m-4">
    {{$departments->links()}}
  </div>
  
  


</div>