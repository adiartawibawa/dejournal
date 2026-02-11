<?php

namespace App\Filament\Resources\Siswas\Pages;

use App\Filament\Imports\SiswaImporter;
use App\Filament\Resources\Siswas\SiswaResource;
use App\Models\User;
use Filament\Actions\CreateAction;
use Filament\Actions\ImportAction;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Hash;

class ListSiswas extends ListRecords
{
    protected static string $resource = SiswaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ImportAction::make()
                ->importer(SiswaImporter::class)
                ->label('Import Excel')
                ->color('success')
                ->icon('heroicon-o-arrow-down-tray'),

            CreateAction::make()
                ->label('Tambah Siswa')
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
                    $user->assignRole('siswa');

                    // Buat data siswa
                    $siswaData = $data;
                    unset($siswaData['user'], $siswaData['password']);
                    $siswaData['user_id'] = $user->id;

                    return $model::create($siswaData);
                }),
        ];
    }
}
