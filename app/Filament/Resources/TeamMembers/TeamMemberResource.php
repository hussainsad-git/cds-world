<?php

namespace App\Filament\Resources\TeamMembers;

use App\Enums\TeamGroup;
use App\Filament\Resources\TeamMembers\Pages\CreateTeamMember;
use App\Filament\Resources\TeamMembers\Pages\EditTeamMember;
use App\Filament\Resources\TeamMembers\Pages\ListTeamMembers;
use App\Filament\Support\SeoFields;
use App\Models\TeamMember;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\SpatieMediaLibraryImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use UnitEnum;

class TeamMemberResource extends Resource
{
    protected static ?string $model = TeamMember::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedUserGroup;

    protected static string|UnitEnum|null $navigationGroup = 'Site';

    protected static ?string $navigationLabel = 'Team Members';

    protected static ?int $navigationSort = 1;

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Profile')->columns(2)->schema([
                TextInput::make('name')->required()->maxLength(255),
                TextInput::make('designation')->maxLength(255),
                Select::make('group')->label('Section')->options(TeamGroup::options())->required()->default(TeamGroup::Staff->value),
                TextInput::make('email')->email()->maxLength(255),
                SpatieMediaLibraryFileUpload::make('photo')->collection('photo')->image()->imageEditor()->columnSpanFull(),
                Textarea::make('bio')->label('Biography')->rows(6)->columnSpanFull()->helperText('Plain text. Line breaks are preserved on the website.'),
                Repeater::make('social_links')->label('Social links')->columnSpanFull()->defaultItems(0)->addActionLabel('Add link')
                    ->schema([
                        Select::make('platform')->options(['linkedin' => 'LinkedIn', 'x' => 'X (Twitter)', 'facebook' => 'Facebook', 'instagram' => 'Instagram', 'website' => 'Website'])->required(),
                        TextInput::make('url')->url()->required()->maxLength(2048),
                    ])->columns(2),
            ]),
            Section::make('Display')->columns(3)->schema([
                TextInput::make('sort_order')->numeric()->default(0)->helperText('Lower numbers appear first.'),
                Toggle::make('is_active')->label('Visible on website')->default(true),
                Toggle::make('show_on_home')->label('Show on homepage'),
            ]),
            Section::make('SEO')->collapsed()->schema([SeoFields::make()])
                ->visible(fn () => auth()->user()?->can('manage_seo') ?? false),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('sort_order')
            ->reorderable('sort_order')
            ->columns([
                SpatieMediaLibraryImageColumn::make('photo')->collection('photo')->conversion('thumb')->circular(),
                TextColumn::make('name')->searchable()->sortable()->description(fn (TeamMember $record) => $record->designation),
                TextColumn::make('group')->label('Section')->badge()->formatStateUsing(fn ($state) => $state instanceof TeamGroup ? $state->label() : $state)->sortable(),
                IconColumn::make('is_active')->boolean()->label('Visible'),
                IconColumn::make('show_on_home')->boolean()->label('Home'),
            ])
            ->filters([SelectFilter::make('group')->label('Section')->options(TeamGroup::options())])
            ->recordActions([EditAction::make(), DeleteAction::make()])
            ->toolbarActions([BulkActionGroup::make([DeleteBulkAction::make()])]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListTeamMembers::route('/'),
            'create' => CreateTeamMember::route('/create'),
            'edit' => EditTeamMember::route('/{record}/edit'),
        ];
    }
}
