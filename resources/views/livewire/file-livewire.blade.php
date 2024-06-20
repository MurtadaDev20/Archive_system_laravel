<div>
    <div class="row">
        <div class="col-md-12 mb-30">
          <div class="card card-statistics h-100">
            <div class="card-body">
              <div class="card-body">
                <h5 class="card-title">Add New File</h5>
                <form wire:submit.prevent="save">

                  <div class="mb-3">
                    <label class="form-label" for="exampleInputEmail1">File Name</label>
                    <input wire:model="fileName" type="text" class="form-control" aria-describedby="emailHelp"
                      placeholder="IT">
                    @error('fileName') <span class="text-danger">{{ $message }}</span> @enderror
                  </div>

                  <div class="mb-3">
                    <label class="form-label" for="exampleInputEmail1">Folder Name</label>
                    <select wire:model="selectFolder" class="form-control p-2" id="inlineFormSelectPref">
                      <option value="" selected>Choose...</option>
                      @foreach ($folderName as $folder)
                      @if (Auth::user()->id == $folder->user_id ||Auth::user()->manager_id == $folder->user_id)
                      <option value="{{$folder->id}}" >{{$folder->folder_name}}</option>
                      @endif
                      @endforeach
                      
                    </select>
                    @error('selectFolder') <span class="text-danger">{{ $message }}</span> @enderror
                  </div>

                  <div class="mb-3">
                    <label class="form-label" for="exampleInputEmail1">Attached</label>
                    <input wire:model="attached" type="file" class="form-control" aria-describedby="emailHelp"
                      placeholder="IT">
                    @error('attached') <span class="text-danger">{{ $message }}</span> @enderror
                  </div>

                  <div wire:loading wire:target="attached">
                    Uploading... 
                    </div>
                  <div>
                    <button type="submit" wire:loading.class='btn btn-danger'  wire:loading.attr='disabled' class="btn btn-primary">Add</button>
                  </div>
                 
                 
                </form>
              </div>
            </div>
          </div>
        </div>
      </div>
</div>
