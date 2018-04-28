<?php

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class SetupSeeder extends Seeder
{
    public function run()
    {
        // Reset cached roles and permissions
        app()['cache']->forget('spatie.permission.cache');

        // create permissions
        Permission::create(['name' => 'create permissions']);
        Permission::create(['name' => 'read permissions']);
        Permission::create(['name' => 'update permissions']);
        Permission::create(['name' => 'delete permissions']);

        Permission::create(['name' => 'create users']);
        Permission::create(['name' => 'read users']);
        Permission::create(['name' => 'update users']);
        Permission::create(['name' => 'delete users']);

        // create roles and assign created permissions

        $role = Role::create(['name' => 'developer']);
        $role->givePermissionTo(Permission::all());

        $role = Role::create(['name' => 'admin']);
        $role->givePermissionTo([
            'create users',
            'read users',
            'update users',
            'delete users'
        ]);

        $user = \App\User::create([
            'name' => 'Marty Sloan',
            'email' => 'martinsloan58@gmail.com',
            'phone_number' => env('MARTY_TELE'),
            'password' => bcrypt('ilovebudlight')
        ]);
        $user->assignRole('admin');
        $user->assignRole('developer');

        $user = \App\User::create([
            'name' => 'Jason Furtek',
            'email' => 'jasonfurtek@yahoo.com',
            'phone_number' => env('JASON_TELE'),
            'password' => bcrypt('ilovebudlight')
        ]);
        $user->assignRole('admin');

        $user = \App\User::create([
            'name' => 'Kyle Heller',
            'email' => 'kyle.helltek@gmail.com',
            'phone_number' => env('KYLE_TELE'),
            'password' => bcrypt('ilovebudlight')
        ]);
        $user->assignRole('admin');


        Role::create(['name' => 'user']);

    }
}
