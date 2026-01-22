<?php

namespace App\Filament\Resources\AdminUsers\Pages;

use App\Enums\UserRole;
use App\Filament\Resources\AdminUsers\AdminUserResource;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;

class CreateAdminUser extends CreateRecord
{
    protected static string $resource = AdminUserResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['role'] = UserRole::Admin;
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
