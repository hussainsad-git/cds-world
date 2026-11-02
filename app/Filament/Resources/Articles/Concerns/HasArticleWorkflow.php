<?php

namespace App\Filament\Resources\Articles\Concerns;

use App\Enums\ContentStatus;
use App\Models\Article;
use Filament\Actions\Action;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;

/** Status transitions. Status is never edited freely: it only changes through these actions. */
trait HasArticleWorkflow
{
    private static function transition(Article $record, ContentStatus $to, string $log, array $extra = []): void
    {
        $record->update(['status' => $to] + $extra);
        activity('workflow')->performedOn($record)->causedBy(auth()->user())->log($log);
        Notification::make()->title($log)->success()->send();
    }

    public static function submitAction(): Action
    {
        return Action::make('submit')
            ->label('Submit for review')->icon('heroicon-o-paper-airplane')->color('warning')
            ->requiresConfirmation()
            ->visible(fn (Article $r) => in_array($r->status, [ContentStatus::Draft, ContentStatus::Rejected], true) && auth()->user()->can('update', $r))
            ->action(fn (Article $r) => self::transition($r, ContentStatus::PendingReview, 'Submitted for review', ['submitted_at' => now(), 'review_notes' => null]));
    }

    public static function approveAction(): Action
    {
        return Action::make('approve')
            ->label('Approve')->icon('heroicon-o-check-circle')->color('info')
            ->requiresConfirmation()
            ->visible(fn (Article $r) => $r->status === ContentStatus::PendingReview && auth()->user()->can('approve_article'))
            ->action(fn (Article $r) => self::transition($r, ContentStatus::Approved, 'Approved', ['reviewed_by' => auth()->id(), 'reviewed_at' => now()]));
    }

    public static function rejectAction(): Action
    {
        return Action::make('reject')
            ->label('Request changes')->icon('heroicon-o-x-circle')->color('danger')
            ->schema([Textarea::make('review_notes')->label('Notes for the author')->required()->maxLength(2000)])
            ->visible(fn (Article $r) => $r->status === ContentStatus::PendingReview && auth()->user()->can('approve_article'))
            ->action(fn (Article $r, array $data) => self::transition($r, ContentStatus::Rejected, 'Changes requested', ['review_notes' => $data['review_notes'], 'reviewed_by' => auth()->id(), 'reviewed_at' => now()]));
    }

    public static function publishAction(): Action
    {
        return Action::make('publish')
            ->label('Publish now')->icon('heroicon-o-globe-alt')->color('success')
            ->requiresConfirmation()
            ->visible(fn (Article $r) => in_array($r->status, [ContentStatus::Approved, ContentStatus::Scheduled, ContentStatus::PendingReview, ContentStatus::Draft], true) && auth()->user()->can('publish_article'))
            ->action(fn (Article $r) => self::transition($r, ContentStatus::Published, 'Published', ['published_at' => now(), 'reviewed_by' => $r->reviewed_by ?? auth()->id()]));
    }

    public static function scheduleAction(): Action
    {
        return Action::make('schedule')
            ->label('Schedule')->icon('heroicon-o-clock')->color('primary')
            ->schema([DateTimePicker::make('published_at')->label('Publish at')->required()->seconds(false)->minDate(now()->addMinute())])
            ->visible(fn (Article $r) => in_array($r->status, [ContentStatus::Approved, ContentStatus::Scheduled, ContentStatus::PendingReview], true) && auth()->user()->can('schedule_article'))
            ->action(fn (Article $r, array $data) => self::transition($r, ContentStatus::Scheduled, 'Scheduled for '.\Illuminate\Support\Carbon::parse($data['published_at'])->format('d M Y H:i'), ['published_at' => $data['published_at']]));
    }

    public static function unpublishAction(): Action
    {
        return Action::make('unpublish')
            ->label('Unpublish')->icon('heroicon-o-eye-slash')->color('gray')
            ->requiresConfirmation()
            ->visible(fn (Article $r) => in_array($r->status, [ContentStatus::Published, ContentStatus::Scheduled], true) && auth()->user()->can('publish_article'))
            ->action(fn (Article $r) => self::transition($r, ContentStatus::Draft, 'Unpublished'));
    }

    /** @return array<Action> */
    public static function workflowActions(): array
    {
        return [self::submitAction(), self::approveAction(), self::rejectAction(), self::scheduleAction(), self::publishAction(), self::unpublishAction()];
    }
}
