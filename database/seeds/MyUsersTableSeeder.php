<?php

use Illuminate\Database\Seeder;

class MyUsersTableSeeder extends Seeder
{
    public function run()
    {
        factory(App\User::class, 10)->create()->each(function ($u) {
            $u->assignRole('user');

            $faker = \Faker\Factory::create();
            factory(\App\Models\TimeEntry::class, 50)->create([
                'user_id' => $u->id,
                'created_at' => $faker->dateTimeBetween(\Carbon\Carbon::now()->subDays(7), \Carbon\Carbon::now()->endOfDay())
            ]);
        });
    }
}
