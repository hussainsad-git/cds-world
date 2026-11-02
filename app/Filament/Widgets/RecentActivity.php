<?php

namespace App\Filament\Widgets;

use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;
use Spatie\Activitylog\Models\Activity;

class RecentActivity extends TableWidget
{
    protected static ?int $sort = 4;

    protected int|string|array $columnSpan = 'full';

    public static function canView(): bool
    {
        return auth()->user()?->can('view_activity_log') ?? false;
    }

    public function table(Table $table): Table
    {
        return $table
            ->heading('Recent activity')
            ->query(Activity::query()->with('causer')->latest()->limit(10))
            ->paginated(false)
            ->columns([
                TextColumn::make('created_at')->since()->label('When'),
                TextColumn::make('causer.name')->label('Who')->placeholder('System'),
                TextColumn::make('log_name')->badge()->label('Area'),
                TextColumn::make('description'),
                TextColumn::make('subject_type')->label('Record')->formatStateUsing(fn ($state, Activity $record) => $state ? class_basename($state).' #'.$record->subject_id : '—'),
            ]);
    }
}
