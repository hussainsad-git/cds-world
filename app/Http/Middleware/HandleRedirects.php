<?php

namespace App\Http\Middleware;

use App\Models\Redirect;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\HttpFoundation\Response;

/** Applies Redirects (301/302/307/308) and 410 Gone entries before routing. Skips the admin and asset paths. */
class HandleRedirects
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! $request->isMethod('GET') && ! $request->isMethod('HEAD')) {
            return $next($request);
        }

        $path = '/'.trim($request->path(), '/');
        if ($path === '/admin' || str_starts_with($path, '/admin/') || str_starts_with($path, '/livewire') || str_starts_with($path, '/storage/')) {
            return $next($request);
        }

        $map = Cache::remember('redirects.map', 600, fn () => Redirect::query()->where('is_active', true)->get(['id', 'from_path', 'to_url', 'status_code'])->keyBy('from_path')->map(fn ($r) => $r->only(['id', 'to_url', 'status_code']))->all());

        $hit = $map[$path] ?? $map[rtrim($path, '/')] ?? null;
        if (! $hit) {
            return $next($request);
        }

        Redirect::whereKey($hit['id'])->update(['hits' => \DB::raw('hits + 1'), 'last_hit_at' => now()]);

        if ((int) $hit['status_code'] === 410) {
            abort(410);
        }

        $to = (string) $hit['to_url'];
        $to = preg_match('~^https?://~i', $to) ? $to : url($to);

        return redirect()->to($to, (int) $hit['status_code']);
    }
}
