<?php

use Illuminate\Database\Seeder;

class CategoriesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run(\Faker\Generator $faker)
    {
        $catgories = factory(\App\Models\Category::class, 50)->make()->each(function ($user) use ($faker) {
            $user->pid = $faker->randomElement(range(0, 30));
        })->toArray();
        \App\Models\Category::query()->insert($catgories);
    }
}
