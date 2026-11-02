@php
    $src = $d['source'] ?? 'youtube'; $url = $d['url'] ?? '';
    $id = $src === 'youtube' ? \App\Models\Video::parseExternalId('youtube', $url) : null;
    $embed = $src === 'youtube' && $id ? 'https://www.youtube-nocookie.com/embed/'.$id.'?autoplay=1&rel=0' : ($url ? 'https://www.facebook.com/plugins/video.php?show_text=false&autoplay=1&href='.urlencode($url) : null);
@endphp
@if ($embed)
<section class="section"><div class="container-cds container-prose">
    @if ($section->heading)<h2 class="section-title">{{ $section->heading }}</h2>@endif
    <x-video-facade :embed="$embed" :thumb="$id ? 'https://i.ytimg.com/vi/'.$id.'/hqdefault.jpg' : null" :title="$section->heading ?: 'Video'" />
    @if (!empty($d['caption']))<p class="small text-muted mt-2">{{ $d['caption'] }}</p>@endif
</div></section>
@endif
