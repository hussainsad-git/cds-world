<?php

namespace Database\Seeders;

use App\Models\WebsiteSetting;
use Illuminate\Database\Seeder;

class SettingsSeeder extends Seeder
{
    public function run(): void
    {
        $defaults = [
            'general' => [
                'site_name' => 'CDS World',
                'tagline' => 'Advancing Stability Through Developmental Research',
                // TODO(owner): confirm legal name — live site uses both "Development and Stability" and "Development Studies".
                'organization_name' => 'Centre for Development and Stability',
                'organization_description' => 'A non-governmental organization dedicated to youth engagement, national dialogue, and policy development.',
                'contact_email' => 'info@cdsworld.org',
                'phones' => ['+92 312 9402354', '+92 311 5148607'],
                'address' => '',
                'copyright' => '© {year} CDSworld. All rights reserved.',
                'announcement_enabled' => '0',
                'announcement_text' => '',
                'announcement_url' => '',
            ],
            'social' => [
                'facebook' => 'https://www.facebook.com/share/16eB9bA51g/',
                'instagram' => 'https://www.instagram.com/centrefordevelopmenstability/',
                'whatsapp' => 'https://wa.me/923129402354',
                'x' => '', 'youtube' => '', 'linkedin' => '',
            ],
            'theme' => [
                'preset' => 'cds-green',
                'color_primary' => '#01411c',
                'color_secondary' => '#0b7a3e',
                'color_accent' => '#8fe0b1',
                'color_bg' => '#ffffff',
                'color_bg_alt' => '#eaf3ec',
                'color_text' => '#1b241f',
                'color_muted' => '#56625b',
                'header_style' => 'light',
                'footer_style' => 'dark',
                'button_style' => 'solid',
                'font_heading' => 'Fraunces',
                'font_body' => 'Inter',
                'font_size_base' => '17',
                'line_height' => '1.65',
                'radius' => '16',
                'container_width' => '1200',
            ],
            'seo' => [
                'default_title_suffix' => ' | CDS World',
                'default_og_image' => '',
                'sitemap_enabled' => '1',
                'robots_extra' => '',
            ],
            'newsletter' => [
                'admin_notify_email' => 'info@cdsworld.org',
                'consent_text' => 'I agree to receive occasional emails from CDS World and understand I can unsubscribe at any time.',
            ],
        ];

        foreach ($defaults as $group => $values) {
            foreach ($values as $key => $value) {
                WebsiteSetting::firstOrCreate(
                    ['group' => $group, 'key' => $key],
                    ['value' => is_array($value) ? json_encode($value) : $value, 'type' => is_array($value) ? 'json' : 'string'],
                );
            }
        }
    }
}
