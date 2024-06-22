<div>

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
                                <Label class="m-3 fs-3">By Date : </Label>
                                <div class="mx-2">

                                    <div class="input-group">
                                        <input wire:model.lazy="from" type="date" class="form-control "
                                            placeholder="YYYY-MM-DD">
                                        <span class="input-group-addon">To</span>
                                        <input wire:model.lazy="to" type="date" class="form-control"
                                            placeholder="YYYY-MM-DD">
                                    </div>
                                </div>

                                <Label class="m-3 fs-3">By Name : </Label>
                                <div class="widget-search ml-0 clearfix">
                                    <input wire:model.lazy="searchByName" type="search" class="form-control"
                                        placeholder="Search....">
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
                                        <th>File Name</th>
                                        <th>Folder Name</th>
                                        <th>Add By</th>
                                        <th>Created At</th>
                                        <th>Updated At</th>
                                        <th>Status</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($files as $file )

                                    @if (Auth::user()->manager_id ==$file->folder->user_id || Auth::user()->id == $file->folder->user_id)
                                    <tr>
                                        <td>{{$num++}}</td>
                                        <td>
                                            {{ $file->file_name }}<br>
                                            <a onclick="copyToClipboard('{{ $file->code }}')" style="cursor: pointer;">
                                                <span style="font-weight: bold; font-size: smaller;">{{'-'. $file->code . '-' }}</span>
                                            </a>
                                        </td>
                                        <td>{{$file->folder->folder_name}}</td>
                                        <td>{{$file->user->name}}</td>
                                        <td>{{$file->created_at}}</td>
                                        <td> {{$file->updated_at}}</td>
                                        <td><label class="badge bg-success">Approved</label></td>

                                        <td>
                                            @if (Auth::user()->id == $file->user_id)
                                            <button class="btn btn-outline-danger btn-sm"  title="Delete"
                                                 data-toggle="modal" data-target="#deleteModal"><i class="fa fa-trash"></i></button>

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
                                                    <button wire:click="deleteFile({{$file->id}})" type="button"
                                                      class="btn btn-danger">Delete</button>
                                                  </div>
                                                </div>
                                              </div>
                                            </div>
                                            @endif
                                            
                                            <button wire:click="downloadFile({{$file->id}})"
                                                class="btn btn-outline-warning btn-sm" title="download"><i class="fa fa-download"></i>
                                            </button>
                                            <button onclick="window.location.href='{{ route('viewFile', $file->id) }}'" class="btn btn-outline-success btn-sm" title="View">
                                                <i class="fa fa-eye"></i>
                                            </button>

                                            
                                        </td>
                                        

                                    </tr>
                                    @endif
                                    @endforeach
                                </tbody>

                            </table>
                            <hr>
                            {{ $files->links() }}
                        </div>
                    </div>
                </div>
            </div>


        </div>
        <livewire:scripts />
        <script>
            function copyToClipboard(code) {
                var textarea = document.createElement('textarea');
                textarea.value = code;
                document.body.appendChild(textarea);
                textarea.select();
                document.execCommand('copy');
                document.body.removeChild(textarea);
                toastr.info('Code copied to clipboard: ' + code);
            }
        </script>