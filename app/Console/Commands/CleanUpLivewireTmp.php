<?php

namespace App\Console\Commands;

use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class CleanUpLivewireTmp extends Command
{
    protected $signature = 'livewire:cleanup-tmp';
    protected $description = 'Clean up old Livewire temporary files';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        $files = Storage::disk('local')->files('livewire-tmp'); 
        $expiration = Carbon::now()->subMinutes(5); // Files older than 5 min
        foreach ($files as $file) {
            if (Storage::disk('local')->lastModified($file) < $expiration->timestamp) {
                Storage::disk('local')->delete($file);
            }
        }

        $this->info('Old Livewire temporary files cleaned up successfully.');
    }
}
