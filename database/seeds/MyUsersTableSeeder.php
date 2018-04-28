<?php

use Illuminate\Database\Seeder;

class MyUsersTableSeeder extends Seeder
{
    public function run()
    {
        factory(App\User::class, 10)->create()->each(function ($u) {
            $u->assignRole('user');
            factory(\App\Models\TimeEntry::class, 50)->create(['user_id' => $u->id]);
        });
    }
}
