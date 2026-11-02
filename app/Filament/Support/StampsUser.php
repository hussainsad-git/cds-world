<?php

namespace App\Filament\Support;

/** Records created_by / updated_by on Filament create & edit pages. */
trait StampsUser
{
    protected function stampCreate(array $data): array
    {
        $data['created_by'] = auth()->id();
        $data['updated_by'] = auth()->id();

        return $data;
    }

    protected function stampUpdate(array $data): array
    {
        $data['updated_by'] = auth()->id();

        return $data;
    }
}
