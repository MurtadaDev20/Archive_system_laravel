<div>
  @php
    $roles = Auth::user()->roles;
  @endphp
  @foreach ($roles as $role )
      @if ($role->name == 'Manager')
      <div class="row">
        <div class="col-md-12 mb-30">
          <div class="card card-statistics h-100">
            <div class="card-body">
              <div class="card-body">
                <h5 class="card-title">Add New Folder</h5>
                <form>
                  <div class="mb-3">
                    <label class="form-label" for="exampleInputEmail1">Folder</label>
                    <input wire:model="folder" type="text" class="form-control" aria-describedby="emailHelp"
                      placeholder="IT">
                    @error('folder') <span class="text-danger">{{ $message }}</span> @enderror
                  </div>
                  
    
                  @if($editMode)
                  <button wire:click.prevent="updateFolder" class="btn btn-primary">Update</button>
                  <button wire:click.prevent="cancelUpdate" class="btn btn-secondary">Cancel</button>
                  @else
                  <button wire:click.prevent="addFolder" class="btn btn-primary">Add</button>
                  @endif
                </form>
              </div>
            </div>
          </div>
        </div>
      </div>
      @endif
  @endforeach
    
  
    {{-- ==========================================================Folder
    Detiles===================================================== --}}
  
    <div class="row text-center">
      @foreach($folders as $folder)
      @foreach ($roles as $role )
      @if (Auth::user()->id == $folder->user_id || $role->name == 'Admin' ||Auth::user()->manager_id == $folder->user_id)
      <div class="col-sm-3 col-lg-3 col-xl-2 mb-30">
        <div class="card card-statistics h-100">
          <div class="card-body">
            <a href="{{route('manageFileShow',$folder->id)}}" class="text-dark float-end" data-bs-toggle="tooltip" data-bs-placement="left" title=""
            data-bs-original-title="View project"><i class="fa fa-eye"></i> <span>Show File</span> </a>
          
            <h5 class="mt-15 mb-15 text-center" ><b>{{ $folder->folder_name }}</b></h5>
            <div class="text-center"> Created By: {{ $folder->user->name }}</div>
            
            <div class="row">
              <div class="col-12 col-sm-12 mt-30">
                <b>Files</b>
                <h4 class="text-danger mt-10">{{$folder->files->count()}}</h4>
              </div>
            </div>
            @if (Auth::user()->id == $folder->user_id)

            <div class="row">
              <div class="col-12 col-sm-12 mt-2">
                
                <div class="card-body">
                  <a wire:click.prevent="editFolder({{ $folder->id }})" class="button button-border x-small btn-sm"
                    href="#">Edit  </a>
                  <a wire:click.prevent="confirmDelete({{ $folder->id }})" class="button button-border x-small btn-sm"
                    href="#">Delete </a>
                </div>
                
                
              </div>
            </div>
            @else

            @endif
          </div>
        </div>
      </div>
      
      @endif
      
      @endforeach
      @endforeach
      
    </div>
    <div class="m-4">
    {{$folders->links()}}
    </div>
  
  
  </div>