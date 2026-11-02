<?php

namespace App\Filament\Pages;

use App\Models\WebsiteSetting;
use BackedEnum;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Schema;
use UnitEnum;

/**
 * Base for pages that edit `website_settings` groups. Form state is data[group][key].
 * Subclasses declare their groups, boolean keys and the form components.
 */
abstract class BaseSettingsPage extends Page
{
    protected static bool $isDiscovered = false;

    protected string $view = 'filament.pages.settings-form';

    public ?array $data = [];

    protected static string|UnitEnum|null $navigationGroup = 'Site';

    /** @return array<int, string> */
    abstract protected function groups(): array;

    /** @return array<int, string> dotted "group.key" values stored as 0/1 */
    protected function booleans(): array
    {
        return [];
    }

    abstract protected function permission(): string;

    public static function canAccess(): bool
    {
        return (new static)->permission() !== '' && (auth()->user()?->can((new static)->permission()) ?? false);
    }

    public function mount(): void
    {
        $state = [];
        foreach ($this->groups() as $group) {
            $state[$group] = WebsiteSetting::group($group);
        }
        foreach ($this->booleans() as $path) {
            data_set($state, $path, (bool) data_get($state, $path));
        }
        $this->form->fill($state);
    }

    abstract public function form(Schema $schema): Schema;

    public function save(): void
    {
        abort_unless(static::canAccess(), 403);

        $state = $this->form->getState();

        foreach ($this->groups() as $group) {
            foreach ((array) ($state[$group] ?? []) as $key => $value) {
                if (in_array("$group.$key", $this->booleans(), true)) {
                    $value = $value ? '1' : '0';
                }
                WebsiteSetting::put($group, (string) $key, $value ?? '');
            }
        }

        $this->afterSave();

        Notification::make()->success()->title('Settings saved')->send();
    }

    protected function afterSave(): void
    {
        \Illuminate\Support\Facades\Cache::forget('theme.css');
    }
}
