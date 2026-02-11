<?php

namespace App\Filament\Imports;

use App\Models\Siswa;
use App\Models\User;
use Carbon\Carbon;
use Filament\Actions\Imports\ImportColumn;
use Filament\Actions\Imports\Importer;
use Filament\Actions\Imports\Models\Import;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Number;

class SiswaImporter extends Importer
{
    protected static ?string $model = Siswa::class;

    public static function getColumns(): array
    {
        return [
            ImportColumn::make('siswa.nisn')
                ->label('NISN')
                ->rules(['required', 'digits:10', 'unique:siswa,nisn']),

            ImportColumn::make('user.name')
                ->label('Nama Lengkap')
                ->rules(['required', 'max:255']),

            ImportColumn::make('user.email')
                ->label('Email')
                ->rules(['required', 'email', 'max:255', 'unique:users,email']),

            ImportColumn::make('user.username')
                ->label('Username')
                ->rules(['required', 'max:50', 'unique:users,username']),

            ImportColumn::make('siswa.tempat_lahir')
                ->label('Tempat Lahir'),

            ImportColumn::make('user.tanggal_lahir')
                ->label('Tanggal Lahir')
                ->castStateUsing(function ($state) {
                    return Carbon::parse($state);
                }),

            ImportColumn::make('agama')
                ->label('Agama')
                ->rules(['in:Islam,Kristen,Katolik,Hindu,Buddha,Konghucu']),

            ImportColumn::make('user.alamat')
                ->label('Alamat'),

            ImportColumn::make('siswa.nama_ayah')
                ->label('Nama Ayah'),

            ImportColumn::make('siswa.nama_ibu')
                ->label('Nama Ibu'),

            ImportColumn::make('siswa.pekerjaan_orang_tua')
                ->label('Pekerjaan Orang Tua'),

            ImportColumn::make('siswa.alamat_orang_tua')
                ->label('Alamat Orang Tua'),

            ImportColumn::make('siswa.no_telp_orang_tua')
                ->label('No. Telepon Orang Tua'),

            ImportColumn::make('siswa.is_active')
                ->label('Status Aktif')
                ->boolean()
                ->rules(['boolean']),
        ];
    }

    public function resolveRecord(): Siswa
    {
        return Siswa::where('nisn', $this->data['nisn'])->first();
    }

    public function beforeCreate(): void
    {
        // Generate password default
        $password = Hash::make('password123');

        // Buat user terlebih dahulu
        $user = User::create([
            'name' => $this->data['user']['name'],
            'email' => $this->data['user']['email'],
            'username' => $this->data['user']['username'],
            'password' => $password,
            'tanggal_lahir' => $this->data['user']['tanggal_lahir'] ?? null,
            'alamat' => $this->data['user']['alamat'] ?? null,
            'is_active' => true,
        ]);

        $user->assignRole('siswa');

        // Set user_id untuk siswa
        $this->data['user_id'] = $user->id;
        unset($this->data['user']);
    }

    public static function getCompletedNotificationBody(Import $import): string
    {
        $body = 'Import siswa selesai. ' . number_format($import->successful_rows) . ' ' . str('row')->plural($import->successful_rows) . ' diimpor.';

        if ($failedRowsCount = $import->getFailedRowsCount()) {
            $body .= ' ' . number_format($failedRowsCount) . ' ' . str('row')->plural($failedRowsCount) . ' gagal diimpor.';
        }

        return $body;
    }
}
