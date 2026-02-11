<?php

namespace App\Filament\Resources\Siswas\Schemas;

use App\Models\User;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Hash;

class SiswaForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Akun Siswa')
                    ->description('Informasi akun untuk login')
                    ->schema([
                        TextInput::make('user.name')
                            ->label('Nama Lengkap')
                            ->required()
                            ->maxLength(255),

                        TextInput::make('user.email')
                            ->label('Email')
                            ->email()
                            ->required()
                            ->unique(User::class, 'email', ignoreRecord: true)
                            ->maxLength(255),

                        TextInput::make('user.username')
                            ->label('Username')
                            ->required()
                            ->unique(User::class, 'username', ignoreRecord: true)
                            ->maxLength(50),

                        TextInput::make('password')
                            ->label('Password')
                            ->password()
                            ->dehydrateStateUsing(fn($state) => Hash::make($state))
                            ->dehydrated(fn($state) => filled($state))
                            ->required(fn(string $context): bool => $context === 'create')
                            ->maxLength(255)
                            ->helperText(
                                fn(string $context): string =>
                                $context === 'create' ? 'Password untuk login' : 'Kosongkan jika tidak ingin mengubah password'
                            ),
                    ])
                    ->columns(2),

                Section::make('Data Pribadi Siswa')
                    ->schema([
                        TextInput::make('nisn')
                            ->label('NISN')
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->length(10)
                            ->numeric(),

                        TextInput::make('tempat_lahir')
                            ->label('Tempat Lahir')
                            ->maxLength(100),

                        DatePicker::make('user.tanggal_lahir')
                            ->label('Tanggal Lahir')
                            ->maxDate(now()),

                        Select::make('agama')
                            ->options([
                                'Islam' => 'Islam',
                                'Kristen' => 'Kristen',
                                'Katolik' => 'Katolik',
                                'Hindu' => 'Hindu',
                                'Buddha' => 'Buddha',
                                'Konghucu' => 'Konghucu',
                            ])
                            ->searchable(),

                        Textarea::make('user.alamat')
                            ->label('Alamat Siswa')
                            ->rows(3)
                            ->columnSpanFull(),
                    ])
                    ->columns(2),

                Section::make('Data Orang Tua')
                    ->schema([
                        TextInput::make('nama_ayah')
                            ->label('Nama Ayah')
                            ->maxLength(100),

                        TextInput::make('nama_ibu')
                            ->label('Nama Ibu')
                            ->maxLength(100),

                        TextInput::make('pekerjaan_orang_tua')
                            ->label('Pekerjaan Orang Tua')
                            ->maxLength(100),

                        Textarea::make('alamat_orang_tua')
                            ->label('Alamat Orang Tua')
                            ->rows(2),

                        TextInput::make('no_telp_orang_tua')
                            ->label('No. Telepon Orang Tua')
                            ->tel()
                            ->maxLength(20),
                    ])
                    ->columns(2),

                Section::make('Status')
                    ->schema([
                        Toggle::make('is_active')
                            ->label('Aktif')
                            ->default(true)
                            ->inline(false),

                        Toggle::make('user.is_active')
                            ->label('Akun Aktif')
                            ->default(true)
                            ->inline(false),
                    ])
                    ->columns(2),
            ]);
    }
}
