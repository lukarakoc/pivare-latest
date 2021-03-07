<?php

use App\Language;
use Illuminate\Database\Seeder;

class LanguageSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Language::insert([
            [
                'name' => 'Crnogorski',
                'code' => 'me'
            ],
            [
                'name' => 'English',
                'code' => 'en'
            ]
        ]);
    }
}
