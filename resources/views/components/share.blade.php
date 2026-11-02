@props(['url', 'title'])
@php $u = urlencode($url); $t = urlencode($title); @endphp
<div class="share" aria-label="Share">
    <span>Share</span>
    <a href="https://www.facebook.com/sharer/sharer.php?u={{ $u }}" target="_blank" rel="noopener">Facebook</a>
    <a href="https://twitter.com/intent/tweet?url={{ $u }}&text={{ $t }}" target="_blank" rel="noopener">X</a>
    <a href="https://www.linkedin.com/sharing/share-offsite/?url={{ $u }}" target="_blank" rel="noopener">LinkedIn</a>
    <a href="https://wa.me/?text={{ $t }}%20{{ $u }}" target="_blank" rel="noopener">WhatsApp</a>
    <button type="button" class="copy-link" data-url="{{ $url }}">Copy link</button>
</div>
