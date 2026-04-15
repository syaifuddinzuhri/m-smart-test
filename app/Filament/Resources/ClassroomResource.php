<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ClassroomResource\Pages;
use App\Models\Classroom;
use App\Models\Major;
use Filament\Forms;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ClassroomResource extends Resource
{
    protected static ?string $model = Classroom::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $navigationLabel = 'Kelas';

    protected static ?string $modelLabel = 'Kelas';
    protected static ?string $pluralModelLabel = 'Daftar Kelas';

    protected static ?string $navigationGroup = 'Manajemen Peserta';

    protected static ?int $navigationSort = 3;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Informasi Kelas')
                    ->description('Lengkapi data kelas dibawah ini!')
                    ->schema([
                        Select::make('major_id')
                            ->label('Jurusan')
                            ->relationship('major', 'name')
                            ->searchable()
                            ->preload()
                            ->nullable()
                            ->live()
                            ->afterStateUpdated(fn(Get $get, Set $set) => self::updateCode($get, $set)),

                        TextInput::make('name')
                            ->label('Nama Kelas')
                            ->placeholder('Contoh: XI')
                            ->required()
                            ->live(onBlur: true)
                            ->afterStateUpdated(fn(Get $get, Set $set) => self::updateCode($get, $set)),

                        TextInput::make('code')
                            ->label('Kode Kelas')
                            ->placeholder('Otomatis: {Nama}-{Kode Jurusan}')
                            ->required()
                            ->readOnly()
                            ->disabled()
                            ->dehydrated()
                            ->unique(ignoreRecord: true)
                            ->helperText('Format otomatis: Nama Kelas - Kode Jurusan'),

                        Toggle::make('is_active')
                            ->label('Status Aktif')
                            ->default(true),
                    ])
                    ->columns(1)
            ]);
    }

    public static function updateCode(Get $get, Set $set): void
    {
        $name = $get('name');
        $majorId = $get('major_id');

        // Jika Nama ada, tapi Jurusan Kosong
        if ($name && !$majorId) {
            $set('code', strtoupper($name)); // Hanya Nama Kelas (misal: "XI")
        }
        // Jika Nama ada DAN Jurusan dipilih
        elseif ($name && $majorId) {
            $majorCode = Major::find($majorId)?->code;
            if ($majorCode) {
                $set('code', strtoupper("{$name}-{$majorCode}")); // Nama-Kode (misal: "XI-MIPA")
            } else {
                $set('code', strtoupper($name));
            }
        }
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            TextColumn::make('name')
                ->label('Nama Kelas')
                ->searchable(),
            TextColumn::make('major.name')
                ->label('Jurusan')
                ->sortable(),
            TextColumn::make('code')
                ->label('Kode')
                ->searchable(),
            IconColumn::make('is_active')
                ->label('Aktif')
                ->boolean(),
        ])
            ->filters([
                Tables\Filters\SelectFilter::make('major_id')
                    ->label('Filter Jurusan')
                    ->relationship('major', 'name')
            ])
            ->actions([
                Tables\Actions\EditAction::make()->modalWidth('md'),
                Tables\Actions\DeleteAction::make()
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListClassrooms::route('/'),
        ];
    }
}
