<?php

use App\Role;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Role::insert([
            [
                'name' => 'Admin',
                'code' => 'admin'
            ],
            [
                'name' => 'Vlasnik',
                'code' => 'owner'
            ],
            [
                'name' => 'Zaposleni',
                'code' => 'employee'
            ]
        ]);
    }
}
