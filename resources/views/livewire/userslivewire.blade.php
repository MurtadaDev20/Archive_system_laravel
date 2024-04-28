<div>
  <div class="row">
    <div class="col-md-12 mb-30">
      <div class="card card-statistics h-100">
        <div class="card-body">
          <div class="card-body">
            <h5 class="card-title">Add New User</h5>
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


          <button wire:click.prevent="addUser" class="btn btn-primary">Add</button>
          </form>
        </div>
      </div>
    </div>
  </div>
</div>