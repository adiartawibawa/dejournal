<?php

namespace App\Filament\Resources\MataPelajarans;

use App\Filament\Resources\MataPelajarans\Pages\ManageMataPelajarans;
use App\Models\MataPelajaran;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use UnitEnum;

class MataPelajaranResource extends Resource
{
    protected static ?string $model = MataPelajaran::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBookOpen;

    protected static ?string $recordTitleAttribute = 'nama_mapel';

    protected static string|UnitEnum|null $navigationGroup = 'Master Data';

    protected static ?string $navigationLabel = 'Mata Pelajaran';

    protected static ?string $pluralLabel = 'Mata Pelajaran';

    protected static ?int $navigationSort = 3;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('kode_mapel')
                    ->label('Kode Mapel')
                    ->required()
                    ->maxLength(10)
                    ->unique(ignoreRecord: true)
                    ->placeholder('Contoh: MAT-10'),

                TextInput::make('nama_mapel')
                    ->label('Nama Mata Pelajaran')
                    ->required()
                    ->maxLength(100)
                    ->placeholder('Contoh: Matematika'),

                Select::make('kelompok')
                    ->options([
                        'A' => 'Kelompok A (Wajib)',
                        'B' => 'Kelompok B (Wajib)',
                        'C' => 'Kelompok C (Peminatan)',
                        'L' => 'Muatan Lokal',
                        'E' => 'Ekstrakurikuler',
                    ])
                    ->required()
                    ->searchable(),

                TextInput::make('kkm')
                    ->label('KKM')
                    ->numeric()
                    ->minValue(0)
                    ->maxValue(100)
                    ->default(75),

                TextInput::make('jam_per_minggu')
                    ->label('Jam per Minggu')
                    ->numeric()
                    ->minValue(1)
                    ->maxValue(10)
                    ->default(2)
                    ->suffix('jam'),

                Toggle::make('is_active')
                    ->label('Aktif')
                    ->default(true),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('nama_mapel')
            ->columns([
                TextColumn::make('kode_mapel')
                    ->label('Kode')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('nama_mapel')
                    ->label('Nama Mata Pelajaran')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('kelompok')
                    ->label('Kelompok')
                    ->badge()
                    ->color(fn($state) => match ($state) {
                        'A' => 'primary',
                        'B' => 'success',
                        'C' => 'warning',
                        'L' => 'info',
                        'E' => 'danger',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn($state) => match ($state) {
                        'A' => 'Wajib A',
                        'B' => 'Wajib B',
                        'C' => 'Peminatan',
                        'L' => 'Muatan Lokal',
                        'E' => 'Ekstrakurikuler',
                        default => $state,
                    }),

                TextColumn::make('kkm')
                    ->label('KKM')
                    ->numeric()
                    ->sortable(),

                TextColumn::make('jam_per_minggu')
                    ->label('Jam/Minggu')
                    ->numeric()
                    ->suffix(' jam')
                    ->sortable(),

                IconColumn::make('is_active')
                    ->label('Status')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle'),
            ])
            ->filters([
                SelectFilter::make('kelompok')
                    ->options([
                        'A' => 'Kelompok A',
                        'B' => 'Kelompok B',
                        'C' => 'Kelompok C',
                        'L' => 'Muatan Lokal',
                        'E' => 'Ekstrakurikuler',
                    ]),

                TernaryFilter::make('is_active')
                    ->label('Status Aktif'),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('kelompok')
            ->reorderable('nama_mapel');
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageMataPelajarans::route('/'),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::active()->count();
    }
}
