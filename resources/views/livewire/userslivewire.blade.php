<div>
  <div class="row">
    <div class="col-md-12 mb-30">
      <div class="card card-statistics h-100">
        <div class="card-body">
          <div class="card-body">
            <h5 class="card-title">Add New User</h5>
            @php 
            $roless = Auth::user()->roles;
          @endphp
          @foreach ($roless as $role_name)
            @if($role_name->name == 'Admin')

            <form>
              <div class="mb-3">
                <label class="form-label" for="exampleInputEmail1">Full Name</label>
                <input wire:model="fullname" type="text" class="form-control" aria-describedby="emailHelp"
                  placeholder="IT">
                @error('fullname') <span class="text-danger">{{ $message }}</span> @enderror
              </div>
              <div class="mb-3">
                <label class="form-label" for="exampleInputEmail1">Email</label>
                <input wire:model="email" type="email" class="form-control" aria-describedby="emailHelp"
                  placeholder="info@gmail.com">
                @error('email') <span class="text-danger">{{ $message }}</span> @enderror
              </div>
              <div class="mb-3">
                <label class="form-label" for="exampleInputEmail1">Password</label>
                <input wire:model="password" type="password" class="form-control" aria-describedby="emailHelp"
                  placeholder="*******">
                @error('password') <span class="text-danger">{{ $message }}</span> @enderror
              </div>
              <div class="mb-3">
                <label class="form-label" for="exampleInputEmail1">Select Role</label>


                <select wire:model.lazy="roleSelected" class="form-control p-2" id="inlineFormSelectPref">
                  <option selected>Choose...</option>
                  @foreach ($roles as $role)
                  <option value="{{ $role->id}}">{{ $role->name }}</option>
                  @endforeach
                </select>
                @error('roleSelected') <span class="text-danger">{{ $message }}</span> @enderror
              </div>
              @if ($showUserMode)
              <div class="mb-3">
                <label class="form-label" for="exampleInputEmail1">Select Manager</label>
                <select wire:model="selectManager" class="form-control p-2" id="inlineFormSelectPref">
                  <option selected>Choose...</option>
                  @foreach ($manager as $manage)
                  @if($manage->role_id == 3)
                  <option value="{{$manage->users->id}}">{{$manage->users->name}}</option>
                  @endif
                  @endforeach
                  <!-- Add options for managers here -->
                </select>
              </div>
              @endif


              @error('roleSelected') <span class="text-danger">{{ $message }}</span> @enderror
              </div>

              @if($editMode)
              <button wire:click.prevent="updateUser" class="btn btn-primary">Update</button>
              <button wire:click.prevent="cancelUpdate" class="btn btn-secondary">Cancel</button>
              @else
              <button wire:click.prevent="addUser" class="btn btn-primary">Add</button>
              @endif

          </form>

          @elseif ($role_name->name == 'Manager')

            <form>
              <div class="mb-3">
                <label class="form-label" for="exampleInputEmail1">Full Name</label>
                <input wire:model="fullname_manager" type="text" class="form-control" aria-describedby="emailHelp"
                  placeholder="IT">
                @error('fullname_manager') <span class="text-danger">{{ $message }}</span> @enderror
              </div>
              <div class="mb-3">
                <label class="form-label" for="exampleInputEmail1">Email</label>
                <input wire:model="email_manager" type="email" class="form-control" aria-describedby="emailHelp"
                  placeholder="info@gmail.com">
                @error('email_manager') <span class="text-danger">{{ $message }}</span> @enderror
              </div>
              <div class="mb-3">
                <label class="form-label" for="exampleInputEmail1">Password</label>
                <input wire:model="password_manager" type="password" class="form-control" aria-describedby="emailHelp"
                  placeholder="*******">
                @error('password_manager') <span class="text-danger">{{ $message }}</span> @enderror
              </div>
              


             
              </div>
              

              @if($editMode)
              <button wire:click.prevent="updateUser" class="btn btn-primary">Update</button>
              <button wire:click.prevent="cancelUpdate" class="btn btn-secondary">Cancel</button>
              @else
              <button wire:click.prevent="addUserByManager" class="btn btn-primary">Add</button>
              @endif

          </form>
          @else

          @endif
          @endforeach

        </div>
      </div>
    </div>
  </div>

  <div class="page-title">

    <div class="row">
      <div class="col-xl-12 mb-30">
        <div class="card card-statistics h-100">
          <div class="card-body">
            <div class="d-block d-md-flex justify-content-between">
              <div class="d-block">
                <h5 class="card-title pb-0 border-0 mt-3">Data Local</h5>
              </div>
              <div class="d-block d-md-flex clearfix sm-mt-20">



                <div class="widget-search ml-0 clearfix">

                </div>


              </div>
              <div>
                {{-- <button wire:click="sear()" class="button button-border fs-2 btn-sm"><i
                    class="fa fa-search"></i></button> --}}
                {{-- <a class="button button-border fs-2   btn-sm" href="#"><i class="fa fa-search"></i>
                </a> --}}
              </div>

            </div>
            <div class="table-responsive mt-15">
              <table class="table center-aligned-table mb-0">
                <thead>
                  <tr class="text-dark">
                    <th>#</th>
                    <th>Full Name</th>
                    <th>Email</th>
                    <th>Role</th>
                    <th>Created At</th>
                    <th>Updated At</th>
                    <th>Action</th>
                  </tr>
                </thead>
                <tbody>


                  @foreach ($users as $user )

                      @if ($user->role_id == 1)

                          @elseif(Auth::user()->id == $user->users->manager_id)
                              
                                      <tr>
                                        <td>{{$num++}}</td>
                                        <td>{{$user->users->name}}</td>
                                        <td>{{$user->users->email}}</td>
                                        <td>{{$user->role->name}}</td>
                                        <td>{{$user->users->created_at}}</td>
                                        <td> {{$user->users->updated_at}}</td>
                                        <td>
                                          <button class="btn btn-outline-danger btn-sm" data-toggle="modal" data-target="#deleteModal"><i
                                              class="fa fa-trash"></i></button>

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
                                                  Are you sure you want to delete this user?
                                                </div>
                                                <div class="modal-footer">
                                                  <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                                                  <button wire:click='deleteUser({{$user->users->id}})' type="button"
                                                    class="btn btn-danger">Delete</button>
                                                </div>
                                              </div>
                                            </div>
                                          </div>

                                          <button wire:click="editUser({{$user->users->id}})" class="btn btn-outline-warning btn-sm"><i
                                              class="fa fa-edit"></i></button>
                                        </td>

                                      </tr>
                                      
                            @else
                            @php 
                              $roles = Auth::user()->roles;
                            @endphp
                            @foreach ($roles as $role)
                              @if($role->name == 'Admin')
                              <tr>
                                <td>{{$num++}}</td>
                                <td>{{$user->users->name}}</td>
                                <td>{{$user->users->email}}</td>
                                <td>{{$user->role->name}}</td>
                                <td>{{$user->users->created_at}}</td>
                                <td> {{$user->users->updated_at}}</td>
                                <td>
                                  <button class="btn btn-outline-danger btn-sm" data-toggle="modal" data-target="#deleteModal"><i
                                      class="fa fa-trash"></i></button>

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
                                          Are you sure you want to delete this user?
                                        </div>
                                        <div class="modal-footer">
                                          <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                                          <button wire:click='deleteUser({{$user->users->id}})' type="button"
                                            class="btn btn-danger">Delete</button>
                                        </div>
                                      </div>
                                    </div>
                                  </div>

                                  <button wire:click="editUser({{$user->users->id}})" class="btn btn-outline-warning btn-sm"><i
                                      class="fa fa-edit"></i></button>
                                </td>

                              </tr>
                              @endif
                            @endforeach
                                        
                                 
                      @endif

                              

                  @endforeach
                </tbody>


              </table>
              <hr>
              {{ $users->links() }}
            </div>
          </div>
        </div>
      </div>



    </div>