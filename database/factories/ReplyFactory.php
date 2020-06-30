<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Models\Reply;
use Faker\Generator as Faker;

$factory->define(Reply::class, function (Faker $faker) {
    $updatedAt = $faker->dateTimeThisMonth();
    $createdAt = $faker->dateTimeThisMonth($updatedAt);
    return [
        'content' => $faker->sentence,
        'created_at' => $createdAt,
        'updated_at' => $updatedAt,
    ];
});
