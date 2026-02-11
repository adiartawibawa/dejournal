<?php

namespace App\Filament\Resources\Gurus\Pages;

use App\Filament\Resources\Gurus\GuruResource;
use App\Models\User;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Hash;

class ListGurus extends ListRecords
{
    protected static string $resource = GuruResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('Tambah Guru/Staff')
                ->mutateFormDataUsing(function (array $data): array {
                    // Generate username jika kosong
                    if (empty($data['user']['username'])) {
                        $username = strtolower(preg_replace('/[^A-Za-z0-9]/', '', $data['user']['name']));
                        $data['user']['username'] = $username . rand(100, 999);
                    }

                    // Generate password jika kosong
                    if (empty($data['password'])) {
                        $data['password'] = Hash::make('password123'); // default password
                    }

                    return $data;
                })
                ->using(function (array $data, string $model): Model {
                    // Buat user terlebih dahulu
                    $userData = $data['user'];
                    $userData['password'] = $data['password'];

                    $user = User::create($userData);
                    $user->assignRole('guru');

                    // Buat data guru
                    $guruData = $data;
                    unset($guruData['user'], $guruData['password']);
                    $guruData['user_id'] = $user->id;

                    return $model::create($guruData);
                }),
        ];
    }
}
