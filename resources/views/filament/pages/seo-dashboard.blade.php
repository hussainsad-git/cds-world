<x-filament-panels::page>
    <x-filament::section heading="Coverage by content type" description="Counts of records with missing or weak SEO fields. Missing fields fall back to the content title and excerpt on the website.">
        <div class="overflow-x-auto">
            <table class="w-full text-sm text-left">
                <thead class="text-xs uppercase text-gray-500">
                    <tr><th class="py-2 pe-4">Type</th><th class="pe-4">Total</th><th class="pe-4">No SEO title</th><th class="pe-4">No meta description</th><th class="pe-4">Description &gt; 160</th><th class="pe-4">Noindex</th><th>Images without alt</th></tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-white/10">
                @foreach ($summary as $row)
                    <tr>
                        <td class="py-2 pe-4 font-medium">{{ $row['label'] }}</td>
                        <td class="pe-4">{{ $row['total'] }}</td>
                        @foreach (['no_title','no_desc','long_desc','noindex','no_alt'] as $k)
                            <td class="pe-4 {{ $row[$k] > 0 ? 'text-danger-600 font-semibold' : 'text-success-600' }}">{{ $row[$k] }}</td>
                        @endforeach
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    </x-filament::section>

    <x-filament::section heading="Records that need attention" description="Showing up to 60. Open a record and use its SEO tab.">
        @forelse ($issues as $issue)
            <div class="flex items-start justify-between gap-4 py-2 border-b border-gray-200 dark:border-white/10 last:border-0">
                <div>
                    <div class="font-medium">{{ $issue['title'] }} <span class="text-xs text-gray-500">· {{ $issue['type'] }}</span></div>
                    <div class="text-xs text-gray-500">{{ implode(' · ', $issue['problems']) }}</div>
                </div>
                <a href="{{ $issue['url'] }}" class="text-sm text-primary-600 hover:underline shrink-0">Edit</a>
            </div>
        @empty
            <p class="text-sm text-gray-500">No SEO issues found.</p>
        @endforelse
    </x-filament::section>
</x-filament-panels::page>
