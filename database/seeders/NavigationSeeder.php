<?php

namespace Database\Seeders;

use App\Models\NavigationItem;
use App\Models\NavigationMenu;
use Illuminate\Database\Seeder;

class NavigationSeeder extends Seeder
{
    public function run(): void
    {
        // Same URL structure as the live cdsworld.org site (plus /videos).
        $header = NavigationMenu::firstOrCreate(['slug' => 'header'], ['name' => 'Header menu', 'location' => 'header']);
        if ($header->allItems()->doesntExist()) {
            $order = 0;
            foreach ([['Home', '/'], ['Articles', '/articles'], ['Events', '/events'], ['Media', '/media-coverage'], ['Videos', '/videos'], ['Gallery', '/gallery']] as [$label, $url]) {
                NavigationItem::create(['navigation_menu_id' => $header->id, 'label' => $label, 'url' => $url, 'sort_order' => $order++]);
            }
            $about = NavigationItem::create(['navigation_menu_id' => $header->id, 'label' => 'About', 'url' => '#', 'sort_order' => $order++]);
            foreach ([['About Overview', '/about'], ['Who We Are', '/who-we-are'], ['Board of Directors', '/board-of-directors']] as $i => [$label, $url]) {
                NavigationItem::create(['navigation_menu_id' => $header->id, 'parent_id' => $about->id, 'label' => $label, 'url' => $url, 'sort_order' => $i]);
            }
            NavigationItem::create(['navigation_menu_id' => $header->id, 'label' => 'Contact', 'url' => '/contact', 'sort_order' => $order]);
        }

        $footer = [
            'footer-explore' => ['Explore', [['Research Articles', '/articles'], ['Events', '/events'], ['Videos', '/videos'], ['Gallery', '/gallery'], ['Media Coverage', '/media-coverage']]],
            'footer-about' => ['About', [['Overview', '/about'], ['Who We Are', '/who-we-are'], ['Board of Directors', '/board-of-directors'], ['Contact', '/contact']]],
            'footer-legal' => ['Legal', [['Privacy Policy', '/privacy-policy'], ['Terms & Conditions', '/terms-conditions'], ['Disclaimer', '/disclaimer'], ['Editorial Policy', '/editorial-policy']]],
        ];
        foreach ($footer as $slug => [$title, $links]) {
            $menu = NavigationMenu::firstOrCreate(['slug' => $slug], ['name' => 'Footer: '.$title, 'title' => $title, 'location' => 'footer']);
            if ($menu->allItems()->doesntExist()) {
                foreach ($links as $i => [$label, $url]) {
                    NavigationItem::create(['navigation_menu_id' => $menu->id, 'label' => $label, 'url' => $url, 'sort_order' => $i]);
                }
            }
        }
    }
}
