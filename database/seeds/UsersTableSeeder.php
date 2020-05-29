<?php

use Illuminate\Database\Seeder;

class UsersTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run(\Faker\Generator $faker)
    {
        $users = factory(\App\Models\User::class)->times(50)->create();
        $user = \App\Models\User::find(1);
        $user->username = 'Mis';
        $user->phone = 13534256342;
        $user->email = '2543205432@qq.com';
        $user->password = bcrypt('123123');
        $user->save();
    }
}
