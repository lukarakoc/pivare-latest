<?php

use App\Permission;
use Illuminate\Database\Seeder;

class PermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Permission::insert([
            [
                'name' => 'Izmjena iznosa popusta'
            ],
            [
                'name' => 'Izmjena opisa popusta'
            ],
            [
                'name' => 'Izmjena trajanja popusta'
            ]
        ]);
    }
}
