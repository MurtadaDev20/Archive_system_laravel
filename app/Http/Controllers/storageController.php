<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\Request;

class storageController extends Controller
{
    public function showStorage()
    {
            $disk = Storage::disk('local'); 

            $path = storage_path('app'); 
        
            $totalSpace = disk_total_space($path);
            $freeSpace = disk_free_space($path);
            $usedSpace = $totalSpace - $freeSpace;
        
            // Convert bytes to gigabytes for readability
            $totalSpaceGB = $totalSpace / (1024 * 1024 * 1024);
            $freeSpaceGB = $freeSpace / (1024 * 1024 * 1024);
            $usedSpaceGB = $usedSpace / (1024 * 1024 * 1024);

            $formattedtotalSpace = number_format($totalSpaceGB, 0) . 'GB';
            $formattedfreeSpace = number_format($freeSpaceGB, 0) . 'GB';
            $formattedusedSpace = number_format($usedSpaceGB, 0) . 'GB';

        return view('layouts.dashboard', compact([
            'formattedtotalSpace',
            'formattedfreeSpace',
            'formattedusedSpace',
        ]));
    }
}
