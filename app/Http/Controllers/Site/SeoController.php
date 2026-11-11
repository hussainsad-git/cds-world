<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Models\Article;
use App\Models\Event;
use App\Models\GalleryAlbum;
use App\Models\MediaCoverage;
use App\Models\Page;
use App\Models\Video;
use App\Support\Site;
use Illuminate\Support\Facades\Cache;

class SeoController extends Controller
{
    public function sitemap()
    {
        abort_unless((bool) Site::get('seo', 'sitemap_enabled', '1'), 404);

        $xml = Cache::remember('sitemap.xml', 3600, function () {
            $urls = [];
            $add = function (string $path, $lastmod = null, string $freq = 'weekly', string $prio = '0.6') use (&$urls) {
                $urls[] = ['loc' => url($path), 'lastmod' => $lastmod?->toAtomString(), 'freq' => $freq, 'prio' => $prio];
            };

            foreach (Page::live()->with('seo')->get() as $p) {
                if ($p->seo && ! $p->seo->robots_index) {
                    continue;
                }
                $add($p->slug === 'home' ? '/' : '/'.$p->slug, $p->updated_at, $p->slug === 'home' ? 'daily' : 'monthly', $p->slug === 'home' ? '1.0' : '0.7');
            }
            foreach (Article::live()->with('seo')->latestFirst()->get(['id', 'slug', 'updated_at', 'published_at']) as $a) {
                if (! ($a->seo && ! $a->seo->robots_index)) {
                    $add('/articles/'.$a->slug, $a->updated_at, 'monthly', '0.8');
                }
            }
            foreach (Event::live()->get(['slug', 'updated_at']) as $e) {
                $add('/events/'.$e->slug, $e->updated_at, 'weekly', '0.7');
            }
            foreach (Video::live()->get(['slug', 'updated_at']) as $v) {
                $add('/videos/'.$v->slug, $v->updated_at, 'monthly', '0.6');
            }
            foreach (GalleryAlbum::live()->get(['slug', 'updated_at']) as $g) {
                $add('/gallery/'.$g->slug, $g->updated_at, 'monthly', '0.5');
            }
            // Only coverage with its own page; link-only items live on the outlet's site.
            foreach (MediaCoverage::live()->get(['slug', 'updated_at', 'body', 'source_url']) as $m) {
                if (! $m->linksOut()) {
                    $add('/media/'.$m->slug, $m->updated_at, 'monthly', '0.4');
                }
            }

            $out = '<?xml version="1.0" encoding="UTF-8"?>'."\n".'<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">'."\n";
            foreach ($urls as $u) {
                $out .= '<url><loc>'.e($u['loc']).'</loc>'.($u['lastmod'] ? '<lastmod>'.$u['lastmod'].'</lastmod>' : '').'<changefreq>'.$u['freq'].'</changefreq><priority>'.$u['prio'].'</priority></url>'."\n";
            }

            return $out.'</urlset>';
        });

        return response($xml, 200, ['Content-Type' => 'application/xml; charset=UTF-8']);
    }

    public function robots()
    {
        $lines = ['User-agent: *', 'Disallow: /admin', 'Disallow: /livewire', 'Disallow: /newsletter/', 'Disallow: /*?q=', ''];
        if ($extra = trim((string) Site::get('seo', 'robots_extra', ''))) {
            $lines[] = $extra;
            $lines[] = '';
        }
        if ((bool) Site::get('seo', 'sitemap_enabled', '1')) {
            $lines[] = 'Sitemap: '.url('/sitemap.xml');
        }

        return response(implode("\n", $lines)."\n", 200, ['Content-Type' => 'text/plain; charset=UTF-8']);
    }

    /** Theme settings → CSS variables (external file so the CSP needs no inline styles). */
    public function theme()
    {
        $css = Cache::remember('theme.css', 3600, function () {
            $t = \App\Models\WebsiteSetting::group('theme');
            $v = fn ($k, $d) => ($t[$k] ?? '') !== '' ? $t[$k] : $d;
            $radius = (int) $v('radius', 6);
            $btn = $v('button_style', 'solid');

            return ":root{\n"
                .'--c-primary:'.$v('color_primary', '#01411c').";\n"
                .'--c-secondary:'.$v('color_secondary', '#0b7a3e').";\n"
                .'--c-accent:'.$v('color_accent', '#8fe0b1').";\n"
                .'--c-bg:'.$v('color_bg', '#ffffff').";\n"
                .'--c-bg-alt:'.$v('color_bg_alt', '#eaf3ec').";\n"
                .'--c-text:'.$v('color_text', '#1b241f').";\n"
                .'--c-muted:'.$v('color_muted', '#56625b').";\n"
                .'--font-heading:"'.addslashes($v('font_heading', 'Fraunces')).'",Georgia,serif;'."\n"
                .'--font-body:"'.addslashes($v('font_body', 'Inter')).'",system-ui,-apple-system,"Segoe UI",Roboto,sans-serif;'."\n"
                .'--fs-base:'.(int) $v('font_size_base', 17)."px;\n"
                .'--lh-base:'.(float) $v('line_height', 1.65).";\n"
                .'--radius:'.$radius."px;\n"
                .'--container:'.(int) $v('container_width', 1200)."px;\n"
                .'--btn-radius:'.($btn === 'pill' ? '999px' : $radius.'px').";\n"
                ."}\n"
                .($btn === 'outline' ? ".btn-cds{background:transparent;color:var(--c-primary)}.btn-cds:hover{background:var(--c-primary);color:#fff}\n" : '');
        });

        return response($css, 200, ['Content-Type' => 'text/css; charset=UTF-8', 'Cache-Control' => 'public, max-age=300']);
    }
}
