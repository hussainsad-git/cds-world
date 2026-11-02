@if ((bool) \App\Support\Site::get('general', 'announcement_enabled') && \App\Support\Site::get('general', 'announcement_text'))
    <div class="announcement" role="region" aria-label="Announcement">
        <div class="container-cds">
            @if (\App\Support\Site::get('general', 'announcement_url'))
                <a href="{{ \App\Support\Site::get('general', 'announcement_url') }}">{{ \App\Support\Site::get('general', 'announcement_text') }} <span aria-hidden="true">→</span></a>
            @else
                <span>{{ \App\Support\Site::get('general', 'announcement_text') }}</span>
            @endif
        </div>
    </div>
@endif
