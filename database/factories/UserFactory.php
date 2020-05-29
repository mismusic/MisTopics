<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Models\User;
use Faker\Generator as Faker;
use Illuminate\Support\Str;

/*
|--------------------------------------------------------------------------
| Model Factories
|--------------------------------------------------------------------------
|
| This directory should contain each of the model factory definitions for
| your application. Factories provide a convenient way to generate new
| model instances for testing / seeding your application's database.
|
*/

$factory->define(User::class, function (Faker $faker) {
    $updatedAt = $faker->dateTimeThisMonth();
    $createdAt = $faker->dateTimeThisMonth($updatedAt);
    return [
        'username' => $faker->name,
        'phone' => $faker->phoneNumber,
        'avatar' => 'https://cdn.learnku.com/uploads/images/201709/20/1/PtDKbASVcz.png?imageView2/1/w/600/h/600',
        'email' => $faker->unique()->safeEmail,
        'password' => bcrypt(Str::random(10)), // password
        'introduction' => $faker->sentence,
        'remember_token' => Str::random(10),
        'created_at' => $createdAt,
        'updated_at' => $updatedAt,
    ];
});
