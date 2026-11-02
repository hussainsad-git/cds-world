<?php

namespace App\Observers;

use App\Models\Article;

class ArticleObserver
{
    private const KEEP = 30;

    public function saved(Article $article): void
    {
        $tracked = ['title', 'subtitle', 'excerpt', 'body'];
        if (! $article->wasRecentlyCreated && ! $article->wasChanged($tracked)) {
            return;
        }

        $article->revisions()->create([
            'user_id' => auth()->id(),
            'version' => ((int) $article->revisions()->max('version')) + 1,
            'title' => $article->title,
            'subtitle' => $article->subtitle,
            'excerpt' => $article->excerpt,
            'body' => $article->body,
            'status' => $article->status?->value,
        ]);

        // Keep storage small on the shared host: prune the oldest beyond KEEP revisions.
        $old = $article->revisions()->skip(self::KEEP)->take(1000)->pluck('id');
        if ($old->isNotEmpty()) {
            $article->revisions()->whereIn('id', $old)->delete();
        }
    }
}
