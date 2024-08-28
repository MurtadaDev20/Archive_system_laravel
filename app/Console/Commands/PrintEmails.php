<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class PrintEmails extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'print:email {email=info@gmail.com}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Use To Print Your Email';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Print Email Command Successfully');
    }
}
