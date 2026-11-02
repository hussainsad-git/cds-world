<?php

namespace App\Models\Concerns;

use Spatie\Activitylog\Models\Concerns\LogsActivity;
use Spatie\Activitylog\Support\LogOptions;

trait LogsContentActivity
{
    use LogsActivity;

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly($this->loggedAttributes ?? ['title', 'name', 'status', 'published_at', 'is_featured'])
            ->logOnlyDirty()
            ->useLogName(class_basename($this));
    }
}
