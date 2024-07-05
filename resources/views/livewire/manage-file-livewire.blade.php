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
                        @php
                            $showApproveOrReject = false;
                            foreach ($files as $file) {
                                if (Auth::user()->id == $file->folder->user_id) {
                                    $showApproveOrReject = true;
                                    break;
                                }
                            }
                        @endphp
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
                                        @isset($file)
                                        @if (Auth::user()->id == $file->folder->user_id)
                                        <th>Apr Or Rej</th>
                                        @endif
                                        @endisset
                                        
                                        
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($files as $file )

                                    @if (Auth::user()->manager_id ==$file->folder->user_id || Auth::user()->id == $file->folder->user_id)
                                    <tr>
                                        <td>{{$num++}}</td>
                                        <td>
                                            <span style="font-weight: bold;">{{ $file->file_name }}</span><br>
                                            <a onclick="copyToClipboard('{{ $file->code }}')" style="cursor: pointer;">
                                                <span style=" font-size: smaller;">{{'-'. $file->code . '-' }}</span>
                                            </a>
                                        </td>
                                        <td>{{$file->folder->folder_name}}</td>
                                        <td>{{$file->user->name}}</td>
                                        <td>{{$file->created_at}}</td>
                                        <td> {{$file->updated_at}}</td>
                                        <td>
                                            {{-- approved --}}
                                            @if ($file->status_id == 1)
                                                <label class="badge bg-success text-white">{{$file->status->name}}</label></td>

                                            {{-- Waiting --}}
                                            @elseif ($file->status_id == 2)
                                                <label class="badge bg-warning text-white">{{$file->status->name}}</label></td>

                                            {{-- Rejected --}}
                                            @else
                                                <label class="badge bg-danger text-white">{{$file->status->name}}</label></td>
                                            @endif
                                            
                                        
                                        <td>
                                            @if (Auth::user()->id == $file->user_id )
                                            @if ($file->status_id != 1)
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
                                            
                                            @endif
                                            {{-- End Delete --}}
                                            {{-- Download --}}
                                            <button wire:click="downloadFile({{$file->id}})"
                                                class="btn btn-outline-warning btn-sm" title="download"><i class="fa fa-download"></i>
                                            </button>
                                            {{-- View --}}
                                            <button onclick="window.location.href='{{ route('viewFile', $file->id) }}'" class="btn btn-outline-success btn-sm" title="View">
                                                <i class="fa fa-eye"></i>
                                            </button>

                                            
                                        </td>
                                        
                                        {{-- //approved and reject --}}
                                        @if (Auth::user()->id == $file->folder->user_id)
                                            <td >
                                                <button wire:click="approvedFile({{$file->id}})"
                                                    class="btn btn-outline-success btn-sm" title="Approve"><i class="fa fa-thumbs-up"></i>
                                                </button>
                                                <button wire:click="rejectFile({{$file->id}})"
                                                    class="btn btn-outline-danger btn-sm" title="Reject"><i class="fa fa-times"></i>
                                                </button>
                                            </td>
                                        @endif
                                        
                                        

                                    </tr>
                                    @endif
                                    @endforeach
                                </tbody>
                                
                            </table>
                            
                            <hr>
                            <div class="my-auto">
                                <a class="btn btn-outline-primary btn-sm" href="{{route('addFile')}}">Add New File</a>
                            </div>
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
        
        