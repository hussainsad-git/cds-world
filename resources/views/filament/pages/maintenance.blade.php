<x-filament-panels::page>
    <x-filament::section heading="System status">
        <dl class="grid grid-cols-1 sm:grid-cols-2 gap-x-8 gap-y-3 text-sm">
            @foreach ($info as $label => $value)
                <div class="flex justify-between gap-4 border-b border-gray-200 dark:border-white/10 pb-2">
                    <dt class="text-gray-500">{{ $label }}</dt>
                    <dd class="font-medium text-end">{{ $value }}</dd>
                </div>
            @endforeach
        </dl>
    </x-filament::section>

    <x-filament::section heading="Backups" description="Backups are taken at server level so they do not use extra disk inside the application.">
        <div class="prose dark:prose-invert max-w-none text-sm">
            <p>Back up this site's database and the <code>storage/app/public</code> folder only. A full cPanel account backup is not recommended on a shared server with limited disk space.</p>
            <p>Use the commands in <code>deploy/DEPLOY.md</code> (section "Targeted backup") before every deployment and on a schedule.</p>
        </div>
    </x-filament::section>
</x-filament-panels::page>
