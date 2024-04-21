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


                                    <tr>
                                        <td>{{$num++}}</td>
                                        <td>{{$file->file_name}}</td>
                                        <td>{{$file->folder->folder_name}}</td>
                                        <td>{{$file->user->name}}</td>
                                        <td>{{$file->created_at}}</td>
                                        <td> {{$file->updated_at}}</td>
                                        <td><label class="badge bg-success">Approved</label></td>
                                        <td>
                                            {{-- <button wire:click="viewFile({{$file->id}})"
                                                class="btn btn-outline-success btn-sm">View</button> --}}
                                            <button wire:click="downloadFile({{$file->id}})"
                                                class="btn btn-outline-warning btn-sm">Download</button>
                                        </td>
                                        @if($fileContent)
                                        <div>{{ $fileContent }}</div>
                                        @endif

                                    </tr>
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