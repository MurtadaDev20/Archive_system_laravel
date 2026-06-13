<?php

namespace App\Providers;

use App\Models\Category;
use App\Models\Department;
use App\Models\DocumentTransfer;
use App\Models\DocumentType;
use App\Models\File;
use App\Models\Folder;
use App\Models\User;
use App\Policies\CategoryPolicy;
use App\Policies\DepartmentPolicy;
use App\Policies\DocumentTransferPolicy;
use App\Policies\DocumentTypePolicy;
use App\Policies\FilePolicy;
use App\Policies\FolderPolicy;
use App\Policies\UserPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    protected $policies = [
        File::class => FilePolicy::class,
        Folder::class => FolderPolicy::class,
        Department::class => DepartmentPolicy::class,
        User::class => UserPolicy::class,
        Category::class => CategoryPolicy::class,
        DocumentType::class => DocumentTypePolicy::class,
        DocumentTransfer::class => DocumentTransferPolicy::class,
    ];

    public function boot(): void
    {
        //
    }
}
