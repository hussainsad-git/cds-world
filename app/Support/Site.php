<?php

namespace App\Support;

use App\Models\NavigationMenu;
use App\Models\WebsiteSetting;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;

/** Read-only access to site-wide settings, menus and asset URLs for views. */
class Site
{
    public static function get(string $group, string $key, mixed $default = null): mixed
    {
        $v = WebsiteSetting::get($group, $key, $default);

        return ($v === null || $v === '') ? $default : $v;
    }

    public static function name(): string
    {
        return (string) static::get('general', 'site_name', config('app.name'));
    }

    /** Public URL for a file stored on the public disk (settings uploads). */
    public static function file(?string $path): ?string
    {
        return $path ? Storage::disk('public')->url($path) : null;
    }

    public static function copyright(): string
    {
        return str_replace('{year}', (string) now()->year, (string) static::get('general', 'copyright', '© {year} '.static::name()));
    }

    /** @return array<string, array{title: ?string, items: array<int, array<string, mixed>>}> */
    public static function menus(): array
    {
        return Cache::rememberForever('nav.menus.public', function () {
            $out = [];
            $menus = NavigationMenu::query()->where('is_active', true)->get();
            foreach ($menus as $menu) {
                $all = $menu->allItems()->with('page')->where('is_visible', true)->orderBy('sort_order')->get();
                $build = function ($parentId) use (&$build, $all) {
                    return $all->where('parent_id', $parentId)->map(function ($i) use (&$build) {
                        $href = $i->type === 'page' && $i->page
                            ? ($i->page->slug === 'home' ? '/' : '/'.$i->page->slug)
                            : (string) ($i->url ?: '#');

                        return ['label' => $i->label, 'href' => $href, 'new_tab' => (bool) $i->open_in_new_tab, 'children' => $build($i->id)->values()->all()];
                    })->values();
                };
                $out[$menu->slug] = ['title' => $menu->title ?: $menu->name, 'items' => $build(null)->all()];
            }

            return $out;
        });
    }

    public static function menu(string $slug): array
    {
        return static::menus()[$slug]['items'] ?? [];
    }

    public static function menuTitle(string $slug): ?string
    {
        return static::menus()[$slug]['title'] ?? null;
    }

    /** Footer columns = menus whose slug starts with "footer". */
    public static function footerMenus(): array
    {
        return collect(static::menus())->filter(fn ($m, $slug) => str_starts_with($slug, 'footer'))->all();
    }

    public static function isActive(string $href): bool
    {
        $path = '/'.trim((string) parse_url($href, PHP_URL_PATH), '/');
        $cur = '/'.trim(request()->path(), '/');

        return $path === '/' ? $cur === '/' : ($cur === $path || str_starts_with($cur, $path.'/'));
    }
}
