<?php

namespace App\Filament\Resources;

use Filament\Forms;
use App\Models\User;
use Filament\Tables;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Filament\Resources\Resource;
use Illuminate\Support\Facades\Hash;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Section;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\TextInput;
use Filament\Tables\Columns\ImageColumn;
use Filament\Forms\Components\FileUpload;
use Illuminate\Database\Eloquent\Builder;
use Filament\Forms\Components\DateTimePicker;
use App\Filament\Resources\UserResource\Pages;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Resources\UserResource\RelationManagers;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $navigationIcon = 'heroicon-o-user-group';

    protected static ?string $navigationGroup = 'User Settings';

    // protected static ?string $navigationLabel = 'Employees';

    public static function getNavigationLabel(): string
    {
        return __(auth()->user()->hasRole('Admin') ? 'Employees' : 'Profile');
    }

    public static function getLabel(): string
    {
        return __(auth()->user()->hasRole('Admin') ? 'Employees' : 'Profile');
    }
    // protected static ?string $label = 'Employee';

    public static function getNavigationBadge(): ?string
    {
        return auth()->user()->hasRole('Admin') ? static::getModel()::count() : null;
    }


    public static function form(Form $form): Form
    {
        return $form
            ->columns([
                'sm' => 2,
                'md' => 2,
                'xl' => 3,
            ])
            ->schema([
                Section::make('User Information')
                    ->schema([
                        TextInput::make('name')
                            ->placeholder('Enter a name')
                            ->required()
                            ->maxLength(255),
                        TextInput::make('email')
                            ->placeholder('example@mail.com')
                            ->email()
                            ->unique(ignoreRecord: true)
                            ->required()
                            ->maxLength(255),
                        DateTimePicker::make('email_verified_at')->disabled(fn() => !auth()->user()->hasRole('Admin')),
                        TextInput::make('password')
                            ->password()
                            ->revealable()
                            ->dehydrateStateUsing(fn(string $state): string => Hash::make($state))
                            ->dehydrated(fn(?string $state): bool => filled($state))
                            ->required(fn(string $operation): bool => $operation === 'create')
                            ->maxLength(255),
                        Select::make('roles')
                            ->multiple()
                            ->required()
                            ->createOptionForm([
                                TextInput::make('name')
                                    ->required()
                                    ->maxLength(255)
                            ])
                            ->relationship(titleAttribute: 'name')
                            ->preload()
                            ->hidden(fn() => !auth()->user()->hasRole('Admin'))
                            ->columnSpan([
                                'xl' => 2,
                            ]),
                        FileUpload::make('signature_image')
                            ->label('Tanda Tangan')
                            ->disk('public') // Tentukan disk untuk penyimpanan (gunakan public disk)
                            ->directory('signatures') // Direktori untuk menyimpan gambar tanda tangan
                            ->image() // Validasi file yang di-upload hanya gambar
                            ->nullable() // Bisa kosong
                            ->maxSize(1024)->columnSpan([
                                'sm' => 2,
                                'md' => 2,
                                'xl' => 3,
                            ])

                    ]),
                Section::make('Employee Details')
                    ->relationship('employee')
                    ->schema([
                        Select::make('department_id')
                            ->relationship('department', 'name')
                            ->preload()
                            ->searchable()
                            ->createOptionForm([
                                TextInput::make('name')
                                    ->unique()
                                    ->maxLength(255)
                                    ->minLength(2)
                                    ->required(),
                                TextInput::make('description')
                                    ->maxLength(255)
                                    ->minLength(2),
                            ])->required()->disabled(fn() => !auth()->user()->hasRole('Admin')),
                        Select::make('position_id')
                            ->relationship('position', 'name')
                            ->preload()
                            ->searchable()
                            ->createOptionForm([
                                TextInput::make('name')
                                    ->unique()
                                    ->maxLength(255)
                                    ->minLength(2)
                                    ->required(),
                                TextInput::make('description')
                                    ->maxLength(255)
                                    ->minLength(2),
                            ])->required()->disabled(fn() => !auth()->user()->hasRole('Admin')),
                        TextInput::make('contact')
                            ->label('Phone Number')
                            ->placeholder('Enter a phone number')
                            ->required()
                            ->numeric()
                            ->maxLength(255),
                    ]),

            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->searchable(),
                TextColumn::make('roles.name')
                    ->searchable()
                    ->hidden(fn() => !auth()->user()->hasRole('Admin')),
                TextColumn::make('employee.department.name')
                    ->label('Department')
                    ->searchable(),
                TextColumn::make('employee.position.name')
                    ->label('Position')
                    ->searchable(),
                TextColumn::make('employee.contact')
                    ->label('Phone Number')
                    ->searchable(),
                ImageColumn::make('signature_image')
                    ->label('Tanda Tangan'),
                TextColumn::make('email')
                    ->searchable(),
                TextColumn::make('email_verified_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultPaginationPageOption(10)
            ->paginated([10, 25, 50]);;
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->when(!auth()->user()->hasRole('Admin'), function ($query) {
                return $query->where('id', auth()->id());
            });
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'edit' => Pages\EditUser::route('/{record}/edit'),
        ];
    }
}
