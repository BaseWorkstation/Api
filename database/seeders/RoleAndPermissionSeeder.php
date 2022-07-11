<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class RoleAndPermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Schema::disableForeignKeyConstraints();
            DB::table('role_has_permissions')->truncate();
            DB::table('permissions')->truncate();
            DB::table('roles')->truncate();
        Schema::enableForeignKeyConstraints();

        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        /**
        *   Create Permissions
        */
        //Permission::create(['name' => 'see_customer_pin']);
        Permission::create(['name' => 'update_workstation_details']);

        /**
        *   Create Roles then add Permissions
        */
        // base_admin
        $role = Role::create(['name' => 'base_admin']);
        $role->givePermissionTo(Permission::all());

        // team_owner
        $role = Role::create(['name' => 'team_owner']);
        $role->givePermissionTo(Permission::all());

        // workstation_owner
        $role = Role::create(['name' => 'workstation_owner']);
        $role->givePermissionTo(Permission::all());

        // admin
        $role = Role::create(['name' => 'admin']);
        $role->givePermissionTo(Permission::all());

        // customer
        $role = Role::create(['name' => 'customer']);
    }
}
