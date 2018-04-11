<?php

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        \Illuminate\Support\Facades\Artisan::call("migrate:refresh");

         $this->call(SetupSeeder::class);
         $this->call(MyUsersTableSeeder::class);
    }
}
