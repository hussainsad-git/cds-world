<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Event;
use App\Models\EventRegistration;
use App\Support\Listing;
use App\Support\Schema;
use App\Support\Seo;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class EventController extends Controller
{
    public function index(Request $request)
    {
        $tab = $request->query('when', 'upcoming') === 'past' ? 'past' : 'upcoming';
        $q = trim((string) $request->query('q', ''));
        $category = (string) $request->query('category', '');

        $query = Event::live()->with(['media', 'categories']);
        $tab === 'past' ? $query->past() : $query->upcoming();
        if ($q !== '') {
            $like = '%'.str_replace(['%', '_'], ['\\%', '\\_'], $q).'%';
            $query->where(fn ($w) => $w->where('title', 'like', $like)->orWhere('summary', 'like', $like)->orWhere('venue', 'like', $like));
        }
        if ($category !== '') {
            $query->whereHas('categories', fn ($c) => $c->where('slug', $category)->where('type', 'event'));
        }

        $page = Listing::page('events');
        $crumbs = [['Home', '/'], ['Events', '/events']];

        return view('site.events.index', [
            'page' => $page,
            'events' => $query->paginate(9)->withQueryString(),
            'featured' => $tab === 'upcoming' && ! $q && ! $category && ! $request->query('page')
                ? Event::live()->featured()->upcoming()->with('media')->limit(2)->get() : collect(),
            'categories' => Category::active()->ofType('event')->orderBy('name')->get(),
            'tab' => $tab, 'q' => $q, 'category' => $category,
            'crumbs' => $crumbs,
            'seo' => Seo::make($page, [
                'title' => $page?->title ?: 'Events',
                'description' => $page?->excerpt,
                'noindex' => $q !== '',
                'jsonld' => [Schema::breadcrumb($crumbs)],
            ]),
        ]);
    }

    public function show(string $slug)
    {
        $event = Event::live()->where('slug', $slug)->with([
            'speakers.media', 'media', 'categories', 'tags', 'seo',
            'articles' => fn ($q) => $q->live()->latestFirst(),
            'mediaCoverage' => fn ($q) => $q->live()->with('outlet'),
        ])->firstOrFail();
        $image = $event->getFirstMediaUrl('banner', 'large') ?: null;
        $crumbs = [['Home', '/'], ['Events', '/events'], [$event->title, '/events/'.$event->slug]];

        return view('site.events.show', [
            'event' => $event,
            'image' => $image,
            'crumbs' => $crumbs,
            'seo' => Seo::make($event, [
                'title' => $event->title,
                'description' => $event->summary,
                'image' => $image,
                'jsonld' => [Schema::event($event, $image), Schema::breadcrumb($crumbs)],
            ]),
        ]);
    }

    public function register(Request $request, string $slug)
    {
        $event = Event::live()->where('slug', $slug)->firstOrFail();
        abort_unless($event->internal_registration && in_array($event->lifecycle(), ['upcoming', 'ongoing'], true), 404);

        if ($request->filled('website')) { // honeypot
            return back();
        }

        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email:rfc', 'max:190', Rule::unique('event_registrations', 'email')->where('event_id', $event->id)],
            'phone' => ['nullable', 'string', 'max:40'],
            'organization' => ['nullable', 'string', 'max:190'],
            'message' => ['nullable', 'string', 'max:1000'],
            'consent' => ['accepted'],
        ], ['email.unique' => 'This email address is already registered for the event.', 'consent.accepted' => 'Please confirm that we may contact you about this event.']);

        EventRegistration::create([
            'event_id' => $event->id,
            'name' => $data['name'], 'email' => strtolower($data['email']),
            'phone' => $data['phone'] ?? null, 'organization' => $data['organization'] ?? null, 'message' => $data['message'] ?? null,
            'status' => 'registered', 'consented_at' => now(),
            'ip_hash' => hash('sha256', $request->ip().config('app.key')),
        ]);

        return redirect('/events/'.$event->slug.'#register')->with('registered', 'Thank you, your registration has been received.');
    }
}
