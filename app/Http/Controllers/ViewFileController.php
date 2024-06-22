<?php

namespace App\Http\Controllers;

use App\Models\file;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

use function Livewire\store;

class ViewFileController extends Controller
{
    public function view($file)
    {
        try {
            $data = File::find($file);
            $path = Storage::url($data->file);
            // dd($path);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json(['message' => 'File not found'], 404);
        }
        
        return view('layouts.admin.viewFile', ['path' => $path]);
    }
}
