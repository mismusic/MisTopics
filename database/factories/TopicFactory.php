<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Models\Topic;
use Faker\Generator as Faker;

$factory->define(Topic::class, function (Faker $faker) {
    $updatedAt = $faker->dateTimeThisMonth();
    $createdAt = $faker->dateTimeThisMonth($updatedAt);
    return [
        // todo
        'title' => $faker->sentence,
        'content' => $faker->text,
        'description' => $faker->sentence,
        'created_at' => $createdAt,
        'updated_at' => $updatedAt,
    ];
});
