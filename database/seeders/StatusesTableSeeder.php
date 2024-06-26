<?php

namespace Database\Seeders;

use App\Models\Status;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class StatusesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        $statuses = [
            ['name' => 'Approved'],
            ['name' => 'Waiting'],
            ['name' => 'Rejected']
        ];

        foreach ($statuses as $status) {
            Status::create($status);
        }
    }
}
