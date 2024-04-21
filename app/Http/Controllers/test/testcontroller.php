<?php

namespace App\Http\Controllers\test;

use App\Http\Controllers\Controller;
use App\Models\File;
use App\Models\folder;
use App\Models\department;
use App\Models\User;
use App\Models\Role;use Illuminate\Http\Request;

class testcontroller extends Controller
{
    public function index()
{
    $files = File::with('folder', 'department', 'user', 'role')->get();

    // return view('test', compact('files'));

    $files->transform(function ($file) {
        return [
            'id' => $file->id,
                'file_name' => $file->file_name,
                'file' => $file->file,
                'folder_name' => $file->folder->folder_name,
                'department_name' => $file->department->dep_name,
                'user_name' => $file->user->name,
                'role_name' => $file->role->name,
                'created_at' => $file->created_at,
                'updated_at' => $file->updated_at,
                'department' => $file->department->toArray(),
                'folder' => $file->folder->toArray(),
                'user' => $file->user->toArray(),
                'role' => $file->role->toArray(),
        ];
    });
    dd($files);

    
}
}
