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

        \App\User::create([
            'name' => 'Marty Sloan',
            'email' => 'martinsloan58@gmail.com',
            'phone_number' => env('ADMIN_TELEPHONE'),
            'password' => bcrypt('p@$$word!')
        ])->assignRole('developer', 'admin');

        $role = Role::create(['name' => 'user']);

    }
}
