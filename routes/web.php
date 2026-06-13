<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ViewFileController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::get('/', fn () => redirect('/admin'));

Route::group(['prefix' => 'admin', 'middleware' => ['auth']], function () {

    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard')->middleware('role:Admin');
    Route::get('/workspace', [DashboardController::class, 'workspace'])->name('workspace');

    Route::get('/', function () {
        return Auth::user()->hasRole('Admin')
            ? redirect()->route('dashboard')
            : redirect()->route('workspace');
    });

    Route::middleware('role:Admin')->group(function () {
        Route::view('/department', 'layouts.admin.department')->name('departments');
        Route::view('/taxonomy', 'layouts.admin.taxonomy')->name('taxonomy');
    });

    Route::middleware('role:Admin,Manager')->group(function () {
        Route::view('/all-users', 'layouts.admin.allUsers')->name('allUsers');
    });

    Route::view('/folders', 'layouts.admin.folder')->name('folders');
    Route::view('/add-file', 'layouts.admin.file')->name('addFile')->middleware('role:Manager,Employee');
    Route::view('/manage-file', 'layouts.admin.manageFile')->name('manageFile');
    Route::view('/manage-file/{folder_id}', 'layouts.admin.manageFile')->name('manageFileShow');

    Route::get('/documents/{documentId}', fn (int $documentId) => view('layouts.admin.documentShow', compact('documentId')))
        ->name('document.show')
        ->whereNumber('documentId');

    Route::get('/documents/{file}/qr', [ViewFileController::class, 'qr'])
        ->name('document.qr')
        ->whereNumber('file');

    Route::get('/documents/{file}/qr/print', [ViewFileController::class, 'qrPrint'])
        ->name('document.qr.print')
        ->whereNumber('file');

    Route::get('/view-file/{file}', [ViewFileController::class, 'view'])->name('viewFile');
    Route::get('/stream-file/{file}', [ViewFileController::class, 'stream'])->name('streamFile');
});

Auth::routes(['register' => false]);
