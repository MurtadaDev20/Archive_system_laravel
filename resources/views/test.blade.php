@extends('layouts.master')
@section('css')

@section('title')
    empty
@stop
@endsection
@section('page-header')
<!-- breadcrumb -->
<div class="page-title">
    <div class="row">
        <div class="col-sm-6">
            <h4 class="mb-0"> ncvlxcnvxcnvxcv</h4>
        </div>
        <div class="col-sm-6">
            <ol class="breadcrumb pt-0 pr-0 float-left float-sm-right ">
                <li class="breadcrumb-item"><a href="#" class="default-color">Home</a></li>
                <li class="breadcrumb-item active">Page Title</li>
            </ol>
        </div>
    </div>
</div>
<!-- breadcrumb -->
@endsection
@section('content')
<!-- row -->
<div class="row">
    <div class="col-md-12 mb-30">
        <div class="card card-statistics h-100">
            <div class="card-body">
                <table border="1" class="table center-aligned-table text-center mb-0">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>File Name</th>
                            <th>File</th>
                            <th>Folder Name</th>
                            <th>Department Name</th>
                            <th>User Name</th>
                            <th>Role Name</th>
                            <th>Created At</th>
                            <th>Updated At</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($files as $file)
                        <tr>
                            <td>{{ $file->id }}</td>
                            <td>{{ $file->file_name }}</td>
                            <td>{{ $file->file }}</td>
                            <td>{{ $file->folder->folder_name }}</td>
                            <td>{{ $file->department->dep_name }}</td>
                            <td>{{ $file->user->name }}</td>
                            <td>{{ $file->role->name }}</td>
                            <td>{{ $file->created_at }}</td>
                            <td>{{ $file->updated_at }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
                
            </div>
        </div>
    </div>
</div>
<!-- row closed -->
@endsection
@section('js')

@endsection
