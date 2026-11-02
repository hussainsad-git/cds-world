<?php

namespace Database\Seeders;

use App\Enums\CategoryType;
use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $focus = ['Geopolitics', 'Economy & Energy', 'Regional Security', 'CPEC', 'Climate Security', 'International Relations', 'Development & Governance'];

        foreach ($focus as $i => $name) {
            foreach ([CategoryType::Article, CategoryType::ResearchTopic] as $type) {
                Category::firstOrCreate(
                    ['type' => $type->value, 'slug' => \Illuminate\Support\Str::slug($name)],
                    ['name' => $name, 'sort_order' => $i, 'is_active' => true],
                );
            }
        }

        $others = [
            CategoryType::Event->value => ['Conference', 'Seminar', 'Roundtable', 'Webinar', 'Youth Dialogue'],
            CategoryType::MediaCoverage->value => ['Interview', 'Op-Ed', 'News Feature', 'Television'],
            CategoryType::Video->value => ['Interview', 'Panel Discussion', 'Webinar', 'Documentary'],
            CategoryType::Gallery->value => ['Events', 'Conferences', 'Community'],
        ];
        foreach ($others as $type => $names) {
            foreach ($names as $i => $name) {
                Category::firstOrCreate(
                    ['type' => $type, 'slug' => \Illuminate\Support\Str::slug($name)],
                    ['name' => $name, 'sort_order' => $i, 'is_active' => true],
                );
            }
        }
    }
}
