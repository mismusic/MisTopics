<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Models\Category;
use Faker\Generator as Faker;

$factory->define(Category::class, function (Faker $faker) {
    $updatedAt = $faker->dateTimeThisMonth();
    $createdAt = $faker->dateTimeThisMonth($updatedAt);
    return [
        'name' => $faker->unique()->name,
        'description' => $faker->sentence,
        'created_at' => $createdAt,
        'updated_at' => $updatedAt,
    ];
});
