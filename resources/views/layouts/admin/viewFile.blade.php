@extends('layouts.master')
@section('css')
@livewireStyles
@section('title')
All File
@stop
@endsection
@section('page-header')
<!-- breadcrumb -->
<div class="page-title">
    <div class="row">
        <div class="col-sm-6">
            <h4 class="mb-0"> View File </h4>
        </div>
        <div class="col-sm-6">
            <ol class="breadcrumb pt-0 pr-0 float-left float-sm-right ">
                <li class="breadcrumb-item"><a href="#" class="default-color">Home</a></li>
                <li class="breadcrumb-item active"><a href="{{route('manageFile')}}">Manage Files</a> </li>
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
                <div class="row">
                    {{-- <div class="col-md-2"></div> --}}
                    <div class="col-md-12">
                        <iframe style="height: 100vh ; width:100%" src="{{ $path }}" frameborder="0"></iframe>
                    </div>
                </div>
                
            </div>
        </div>
    </div>
</div>

    
            
<!-- row closed -->
@endsection
@section('js')
@livewireScripts
@endsection
