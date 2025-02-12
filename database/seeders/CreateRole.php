<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class CreateRole extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
        /*Permission::create(['name' => '新增服務項目']);
        Permission::create(['name' => '新增服務時段']);
        Permission::create(['name' => '刪除服務項目']);
        Permission::create(['name' => '修改服務項目']);
        Permission::create(['name' => '刪除服務時段']);
        Permission::create(['name' => '修改服務時段']);*/
        $role = Role::create(['name' => '管理員']);
        $role->givePermissionTo(Permission::all());
    }
}
