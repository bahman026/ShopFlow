<?php

declare(strict_types=1);

namespace App\Filament\Resources\UserResource\Pages;

use App\Filament\Resources\UserResource;
use Filament\Resources\Pages\EditRecord;

class EditUser extends EditRecord
{
    protected static string $resource = UserResource::class;

    protected function getHeaderActions(): array
    {
        return [
            //            Actions\DeleteAction::make(),
        ];
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        if (! empty($data['login_token'])) {
            $data['login_token_expire_time'] = now()->addMinutes(5);
        }

        return $data;
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        $data['login_token'] = null;

        return $data;
    }
}
