<?php

namespace Database\Seeders;

use App\Enums\ContentStatus;
use App\Models\Page;
use Illuminate\Database\Seeder;

class PageSeeder extends Seeder
{
    public function run(): void
    {
        foreach (PageContent::all() as $i => $def) {
            $page = Page::withTrashed()->where('slug', $def['slug'])->first();
            if ($page) {
                continue; // never overwrite content edited in the CMS
            }

            $page = Page::create([
                'title' => $def['title'],
                'slug' => $def['slug'],
                'template' => $def['template'] ?? 'default',
                'excerpt' => $def['excerpt'] ?? null,
                'status' => ContentStatus::Published,
                'published_at' => now(),
                'show_in_nav' => false,
                'is_system' => true,
            ]);

            foreach ($def['sections'] as $order => [$type, $heading, $data]) {
                $page->sections()->create([
                    'type' => $type,
                    'heading' => $heading,
                    'data' => $data,
                    'sort_order' => $order,
                    'is_visible' => true,
                ]);
            }

            $page->seo()->create([
                'title' => $def['seo'][0],
                'description' => $def['seo'][1],
                'focus_keyword' => $def['seo'][2] ?? null,
            ]);
        }
    }
}
