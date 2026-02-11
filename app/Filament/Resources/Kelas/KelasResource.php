<?php

namespace App\Filament\Resources\Kelas;

use App\Filament\Resources\Kelas\Pages\ManageKelas;
use App\Models\Guru;
use App\Models\Kelas;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
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

class KelasResource extends Resource
{
    protected static ?string $model = Kelas::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedAcademicCap;

    protected static ?string $recordTitleAttribute = 'nama_kelas';

    protected static string|UnitEnum|null $navigationGroup = 'Master Data';

    protected static ?string $navigationLabel = 'Kelas';

    protected static ?string $pluralLabel = 'Kelas';

    protected static ?int $navigationSort = 2;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('kode_kelas')
                    ->label('Kode Kelas')
                    ->required()
                    ->maxLength(10)
                    ->unique(ignoreRecord: true)
                    ->placeholder('Contoh: X-IPA-1'),

                TextInput::make('nama_kelas')
                    ->label('Nama Kelas')
                    ->required()
                    ->maxLength(50)
                    ->placeholder('Contoh: X IPA 1'),

                Select::make('tingkat')
                    ->options([
                        'X' => 'Kelas 10',
                        'XI' => 'Kelas 11',
                        'XII' => 'Kelas 12',
                    ])
                    ->required()
                    ->searchable(),

                TextInput::make('jurusan')
                    ->label('Jurusan')
                    ->maxLength(50)
                    ->placeholder('Contoh: IPA, IPS, Bahasa'),

                Select::make('wali_kelas_id')
                    ->label('Wali Kelas')
                    ->relationship('waliKelas', 'nama')
                    ->getOptionLabelFromRecordUsing(fn(Guru $record) => $record->nama)
                    ->searchable()
                    ->preload()
                    ->nullable(),

                TextInput::make('kapasitas')
                    ->label('Kapasitas Siswa')
                    ->numeric()
                    ->minValue(1)
                    ->maxValue(50)
                    ->default(30),

                Textarea::make('deskripsi')
                    ->label('Deskripsi')
                    ->rows(3)
                    ->columnSpanFull(),

                Toggle::make('is_active')
                    ->label('Aktif')
                    ->default(true),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('nama_kelas')
            ->columns([
                TextColumn::make('kode_kelas')
                    ->label('Kode')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('full_nama')
                    ->label('Nama Kelas')
                    ->searchable(['nama_kelas', 'jurusan'])
                    ->sortable(),

                TextColumn::make('tingkat')
                    ->label('Tingkat')
                    ->badge()
                    ->color(fn($state) => match ($state) {
                        'X' => 'primary',
                        'XI' => 'success',
                        'XII' => 'warning',
                        default => 'gray',
                    }),

                TextColumn::make('waliKelas.nama')
                    ->label('Wali Kelas')
                    ->searchable()
                    ->sortable()
                    ->placeholder('-'),

                TextColumn::make('jumlah_siswa')
                    ->label('Jumlah Siswa')
                    ->numeric()
                    ->sortable(),

                TextColumn::make('kapasitas')
                    ->label('Kapasitas')
                    ->numeric()
                    ->sortable(),

                IconColumn::make('is_active')
                    ->label('Status')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle'),
            ])
            ->filters([
                SelectFilter::make('tingkat')
                    ->options([
                        'X' => 'Kelas 10',
                        'XI' => 'Kelas 11',
                        'XII' => 'Kelas 12',
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
            ->defaultSort('tingkat')
            ->reorderable('nama_kelas');
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageKelas::route('/'),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::active()->count();
    }
}
