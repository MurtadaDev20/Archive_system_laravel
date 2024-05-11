<div class="container-fluid">
    <div class="row">
        <!-- Left Sidebar start-->
        <div class="side-menu-fixed">
            <div class="scrollbar side-menu-bg">
                <ul class="nav navbar-nav side-menu" id="sidebarnav">
                    <!-- menu item Dashboard-->
                    <li>
                        <a href="{{route('dashboard')}}">
                            <div class="pull-left"><i class="ti-home"></i><span class="right-nav-text">Dashboard</span>
                            </div>
                            <div class="clearfix"></div>
                        </a>
                    </li>
                    <!-- menu title -->
                    <li class="mt-10 mb-10 text-muted pl-4 font-medium menu-title">Components </li>
                    <!-- menu item Elements-->
                    @php
                    $roles = Auth::user()->roles;
                
                    foreach ($roles as $role) {
                        if ($role->name == 'Admin') {
                            echo '<li>
                                <a href="javascript:void(0);" data-toggle="collapse" data-target="#elements">
                                    <div class="pull-left"><i class="ti-palette"></i><span class="right-nav-text">Departments</span></div>
                                    <div class="pull-right"><i class="ti-plus"></i></div>
                                    <div class="clearfix"></div>
                                </a>
                                <ul id="elements" class="collapse" data-parent="#sidebarnav">
                                    <li><a href="' . route('departments') . '">Manage Departments</a></li>
                                </ul>
                            </li>';
                        }
                    }
                @endphp
                    {{-- @endif --}}

                    <li>
                        <a href="javascript:void(0);" data-toggle="collapse" data-target="#Folders">
                            <div class="pull-left"><i class="fa fa-folder"></i><span
                                    class="right-nav-text">Folders</span></div>
                            <div class="pull-right"><i class="ti-plus"></i></div>
                            <div class="clearfix"></div>
                        </a>
                        <ul id="Folders" class="collapse" data-parent="#sidebarnav">
                            <li><a href="{{route('folders')}}">Manage Folders</a></li>
                        </ul>
                    </li>
                    <li>
                        <a href="javascript:void(0);" data-toggle="collapse" data-target="#File">
                            <div class="pull-left"><i class="fa fa-file-pdf-o"></i><span
                                    class="right-nav-text">File</span></div>
                            <div class="pull-right"><i class="ti-plus"></i></div>
                            <div class="clearfix"></div>
                        </a>
                        <ul id="File" class="collapse" data-parent="#sidebarnav">
                            <li><a href="{{route('addFile')}}">Add New File</a></li>
                            <li><a href="{{route('manageFile')}}">Manage File</a></li>
                        </ul>

                    </li>
                    <li>
                        <a href="javascript:void(0);" data-toggle="collapse" data-target="#Users">
                            <div class="pull-left"><i class="fa fa-user-circle-o"></i><span
                                    class="right-nav-text">Users</span></div>
                            <div class="pull-right"><i class="ti-plus"></i></div>
                            <div class="clearfix"></div>
                        </a>
                        <ul id="Users" class="collapse" data-parent="#sidebarnav">
                            <li><a href="{{route('allUsers')}}">All Users</a></li>
                        </ul>

                    </li>


                </ul>
            </div>
        </div>
    </div>
</div>

        <!-- Left Sidebar End-->

        <!--=================================