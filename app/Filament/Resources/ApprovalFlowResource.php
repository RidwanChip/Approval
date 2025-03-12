<?php

namespace App\Filament\Resources;

use Filament\Forms;
use App\Models\User;
use Filament\Tables;
use Filament\Forms\Form;
use Filament\Tables\Table;
use App\Models\ApprovalFlow;
use Filament\Resources\Resource;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Repeater;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\TextInput;
use Illuminate\Database\Eloquent\Builder;
use Filament\Tables\Filters\TrashedFilter;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Resources\ApprovalFlowResource\Pages;
use App\Filament\Resources\ApprovalFlowResource\RelationManagers;


class ApprovalFlowResource extends Resource
{
    protected static ?string $model = ApprovalFlow::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-plus';

    protected static ?string $navigationGroup = 'Approvals';

    protected static ?string $navigationLabel = 'Approvals';

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::where('user_id', auth()->id())->count();
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('name')
                    ->required()
                    ->label('Flow Name')
                    ->placeholder('Enter flow name'),
                Hidden::make('user_id')
                    ->default(auth()->id()),
                Repeater::make('steps')
                    ->relationship()
                    ->required()
                    ->schema([
                        Select::make('user_id')
                            ->label('Approver')
                            ->options(User::all()->pluck('name', 'id'))
                            ->searchable()
                            ->preload()
                            ->required()
                            ->columnStart(1),

                        TextInput::make('level')
                            ->hidden()
                            ->label('Urutan')
                            ->numeric()
                            ->required()
                            ->minValue(1)
                            ->default(function ($state, $get, $set) {
                                // Hitung jumlah step yang sudah ada
                                $steps = $get('../../steps') ?? [];
                                return count($steps) + 1;
                            })
                            ->columnStart(1)
                    ])
                    // ->columns(2)
                    // ->columnSpanFull()
                    ->addActionLabel('Add Another Approver')
                    ->defaultItems(1)
                    ->reorderableWithButtons()
                    ->orderColumn('level')
                    ->label('List Approver')
                    ->mutateRelationshipDataBeforeCreateUsing(function (array $data): array {
                        // Pastikan level diisi
                        $data['level'] = $data['level'] ?? 1;
                        return $data;
                    })
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name'),
                TextColumn::make('steps.user.name')
                    ->label('Approver')
            ])
            ->filters([
                TrashedFilter::make(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
                Tables\Actions\ForceDeleteAction::make(),
                Tables\Actions\RestoreAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\ForceDeleteBulkAction::make(),
                    Tables\Actions\RestoreBulkAction::make(),
                ]),
            ]);
    }

    public static function getEloquentQuery(): Builder
    {

        // if (auth()->user()->hasRole('Admin')) {
        //     return parent::getEloquentQuery();
        // }
        return parent::getEloquentQuery()->where('user_id', auth()->id());

        return parent::getEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
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
            'index' => Pages\ListApprovalFlows::route('/'),
            'create' => Pages\CreateApprovalFlow::route('/create'),
            // 'edit' => Pages\EditApprovalFlow::route('/{record}/edit'),
        ];
    }
}
