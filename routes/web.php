<?php

use App\Http\Controllers\Site\ArticleController;
use App\Http\Controllers\Site\ContactController;
use App\Http\Controllers\Site\EventController;
use App\Http\Controllers\Site\GalleryController;
use App\Http\Controllers\Site\MediaCoverageController;
use App\Http\Controllers\Site\NewsletterController;
use App\Http\Controllers\Site\PageController;
use App\Http\Controllers\Site\SeoController;
use App\Http\Controllers\Site\VideoController;
use Illuminate\Support\Facades\Route;

Route::get('/sitemap.xml', [SeoController::class, 'sitemap']);
Route::get('/robots.txt', [SeoController::class, 'robots']);
Route::get('/theme.css', [SeoController::class, 'theme'])->name('theme.css');

Route::middleware('public.headers')->group(function () {
    Route::get('/', [PageController::class, 'home'])->name('home');

    Route::get('/articles', [ArticleController::class, 'index'])->name('articles.index');
    Route::get('/articles/{slug}', [ArticleController::class, 'show'])->name('articles.show');

    Route::get('/events', [EventController::class, 'index'])->name('events.index');
    Route::get('/events/{slug}', [EventController::class, 'show'])->name('events.show');
    Route::post('/events/{slug}/register', [EventController::class, 'register'])->middleware('throttle:6,1')->name('events.register');

    Route::get('/media-coverage', [MediaCoverageController::class, 'index'])->name('media.index');
    // The previous site published each item at /media/{slug}; those URLs are live again.
    Route::get('/media/{slug}', [MediaCoverageController::class, 'show'])->name('media.show');

    Route::get('/videos', [VideoController::class, 'index'])->name('videos.index');
    Route::get('/videos/{slug}', [VideoController::class, 'show'])->name('videos.show');

    Route::get('/gallery', [GalleryController::class, 'index'])->name('gallery.index');
    Route::get('/gallery/{slug}', [GalleryController::class, 'show'])->name('gallery.show');

    Route::post('/contact', [ContactController::class, 'store'])->middleware('throttle:5,1')->name('contact.store');
    Route::post('/newsletter/subscribe', [NewsletterController::class, 'subscribe'])->middleware('throttle:6,1')->name('newsletter.subscribe');
    Route::get('/newsletter/confirm/{token}', [NewsletterController::class, 'confirm'])->name('newsletter.confirm');
    Route::get('/newsletter/unsubscribe/{token}', [NewsletterController::class, 'unsubscribe'])->name('newsletter.unsubscribe');

    // CMS pages (about, who-we-are, board-of-directors, contact, policies, newsletter, anything added in the admin)
    Route::get('/{slug}', [PageController::class, 'show'])->where('slug', '[A-Za-z0-9_\-]+')->name('page.show');
});
