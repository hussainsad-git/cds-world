@php
    $byGroup = $members->groupBy(fn ($m) => $m->group?->value ?? 'staff');
    $multi = $byGroup->count() > 1;
@endphp
@if ($members->isNotEmpty())
<section class="section">
    <div class="container-cds">
        <x-section-head :title="$section->heading" :text="$d['description'] ?? null" />
        @foreach ($byGroup as $group => $list)
            @if ($multi)<h3 class="group-title">{{ \App\Enums\TeamGroup::from($group)->label() }}</h3>@endif
            <div class="team-grid">
                @foreach ($list as $m)
                    <article class="team-card">
                        <x-media-img :model="$m" collection="photo" conversion="card" :alt="$m->name" ratio="1x1" class="team-photo" />
                        <h4>{{ $m->name }}</h4>
                        <p class="role">{{ $m->designation ?: ' ' }}</p>
                        @if ($m->bio)<p class="bio">{{ \Illuminate\Support\Str::limit(trim(html_entity_decode(strip_tags($m->bio), ENT_QUOTES | ENT_HTML5, 'UTF-8')), 180) }}</p>@endif
                        @if (!empty($m->social_links))
                            <p class="social-row">@foreach ($m->social_links as $l)<a href="{{ $l['url'] }}" rel="noopener" target="_blank">{{ ucfirst($l['platform']) }}</a>@endforeach</p>
                        @endif
                    </article>
                @endforeach
            </div>
        @endforeach
    </div>
</section>
@endif
