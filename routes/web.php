<?php

use App\Http\Controllers\test\testcontroller;
use App\Http\Controllers\ViewFileController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return redirect('/admin');
});

Route::get('/test-pusher', function () {
    return view('pusher_test');
});

Route::group(['prefix' => 'admin', 'middleware' => ['auth']], function () {


    Route::get('/dashboard', function () {return view('layouts.dashboard'); })->name('dashboard')->middleware('role');
    Route::get('/', function () {return view('layouts.dashboard'); })->middleware('role');

    //Main
    Route::get('/master', function () {return view('layouts.master'); });
    //Department
    Route::get('/department', function () {return view('layouts.admin.department'); })->name('departments');
    //Folder
    Route::get('/folders', function () {return view('layouts.admin.folder'); })->name('folders');

    //file
    Route::get('/add-file', function () {return view('layouts.admin.file'); })->name('addFile');
    Route::get('/manage-file', function () { return view('layouts.admin.manageFile'); })->name('manageFile');
    Route::get('/manage-file/{folder_id}', function () {return view('layouts.admin.manageFile'); })->name('manageFileShow');
    Route::get('/view-file/{file}', [ViewFileController::class,'view'])->name('viewFile');

    //Users
    Route::get('/all-users', function () {return view('layouts.admin.allUsers');})->name('allUsers');

    Route::get('/test', [testcontroller::class, 'index']);
});




Auth::routes();
