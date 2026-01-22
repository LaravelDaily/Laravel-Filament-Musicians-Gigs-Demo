<?php

namespace App\Filament\Resources\Musicians\Pages;

use App\Enums\UserRole;
use App\Filament\Resources\Musicians\MusicianResource;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;

class CreateMusician extends CreateRecord
{
    protected static string $resource = MusicianResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['role'] = UserRole::Musician;
        $data['password'] = bcrypt(Str::random(32));
        $data['is_active'] = true;

        return $data;
    }

    protected function afterCreate(): void
    {
        $user = $this->record;

        Password::sendResetLink(['email' => $user->email]);
    }
}
