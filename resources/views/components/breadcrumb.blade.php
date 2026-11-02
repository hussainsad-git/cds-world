@props(['crumbs' => []])
@if (count($crumbs) > 1)
<nav class="cds-breadcrumb" aria-label="Breadcrumb">
    <div class="container-cds">
        <ol>
            @foreach ($crumbs as $i => $c)
                @if ($loop->last)
                    <li aria-current="page">{{ \Illuminate\Support\Str::limit($c[0], 70) }}</li>
                @else
                    <li><a href="{{ url($c[1]) }}">{{ $c[0] }}</a></li>
                @endif
            @endforeach
        </ol>
    </div>
</nav>
@endif
