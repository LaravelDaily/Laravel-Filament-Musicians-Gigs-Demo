<?php

namespace App\Filament\Pages;

use App\Models\Setting;
use BackedEnum;
use DateTimeZone;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Form;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use UnitEnum;

/**
 * @property-read Schema $form
 */
class Settings extends Page
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCog6Tooth;

    protected static ?string $navigationLabel = 'Settings';

    protected static ?string $title = 'System Settings';

    protected static string|UnitEnum|null $navigationGroup = 'System';

    protected static ?int $navigationSort = 110;

    protected string $view = 'filament.pages.settings';

    public ?string $company_name = null;

    public ?string $notification_email = null;

    public ?string $timezone = null;

    public function mount(): void
    {
        $this->company_name = Setting::get('company_name', config('app.name'));
        $this->notification_email = Setting::get('notification_email');
        $this->timezone = Setting::get('timezone', config('app.timezone'));
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Form::make([
                    Section::make('General Settings')
                        ->description('Configure the general settings for your application.')
                        ->schema([
                            TextInput::make('company_name')
                                ->label('Company Name')
                                ->helperText('Used in emails and the portal header.')
                                ->required()
                                ->maxLength(255),
                            TextInput::make('notification_email')
                                ->label('Notification Email')
                                ->helperText('Optional. CC this email on all notifications sent to admins.')
                                ->email()
                                ->maxLength(255),
                            Select::make('timezone')
                                ->label('Default Timezone')
                                ->helperText('Used for displaying dates and times throughout the application.')
                                ->options(fn () => collect(DateTimeZone::listIdentifiers())
                                    ->mapWithKeys(fn (string $tz) => [$tz => $tz])
                                    ->toArray())
                                ->searchable()
                                ->required(),
                        ])
                        ->columns(1),
                ])->statePath(''),
            ]);
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('save')
                ->label('Save Settings')
                ->icon(Heroicon::OutlinedCheck)
                ->action(function (): void {
                    $this->validate([
                        'company_name' => ['required', 'string', 'max:255'],
                        'notification_email' => ['nullable', 'email', 'max:255'],
                        'timezone' => ['required', 'string', 'timezone'],
                    ]);

                    Setting::set('company_name', $this->company_name);
                    Setting::set('notification_email', $this->notification_email);
                    Setting::set('timezone', $this->timezone);

                    Notification::make()
                        ->success()
                        ->title('Settings saved')
                        ->body('Your settings have been saved successfully.')
                        ->send();
                }),
        ];
    }
}
