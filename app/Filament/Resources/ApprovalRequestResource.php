<?php

namespace App\Filament\Resources;

use Filament\Forms;
use App\Models\User;
use Filament\Tables;
use Filament\Forms\Form;
use Filament\Tables\Table;
use App\Models\ApprovalFlow;
use Illuminate\Support\Carbon;
use App\Models\ApprovalRequest;
use Filament\Resources\Resource;
use Filament\Forms\Components\Grid;
use Filament\Tables\Actions\Action;
use Filament\Tables\Filters\Filter;
use Illuminate\Support\Facades\URL;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Section;
use Filament\Tables\Filters\Indicator;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Textarea;
use Filament\Tables\Actions\BulkAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\TextInput;
use Filament\Tables\Actions\ActionGroup;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Forms\Components\DatePicker;
use Filament\Tables\Filters\SelectFilter;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Resources\ApprovalRequestResource\Pages;
use App\Filament\Resources\ApprovalRequestResource\RelationManagers;
use Filament\Support\Enums\FontWeight;

class ApprovalRequestResource extends Resource
{
    protected static ?string $model = ApprovalRequest::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static ?string $navigationLabel = 'List Approvals';

    protected static ?string $navigationGroup = 'Approvals';

    protected static ?string $label = 'Approval';


    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Hidden::make('user_id')
                    ->default(auth()->id()),
                Select::make('approval_flow_id')
                    ->label('Approval Name')
                    ->placeholder('Select an option or Click \'+\' to create a new flow')
                    ->relationship(
                        name: 'flow',
                        titleAttribute: 'name',
                        modifyQueryUsing: fn(Builder $query) => $query->where('user_id', auth()->id())
                    )
                    ->required()
                    ->native(false)
                    ->searchable()
                    ->preload()
                    ->createOptionForm([
                        TextInput::make('name')
                            ->required()
                            ->label('Approval Name'),
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
                    ])
                    ->getOptionLabelUsing(
                        fn($value) => ApprovalFlow::find($value)?->name ?? $value
                    )
                    ->disabledOn('edit'),
                // ->disabled(fn($record) => $record && 'edit' && $record->user_id !== auth()->id()),
                // ->disabled(fn($record) => $record && auth()->user()->canApprove($record)),
                TextInput::make('data')
                    ->required()
                    ->disabled(fn($record) => $record && $record->user_id !== auth()->id()),
                Textarea::make('description')
                    ->required()
                    ->disabled(fn($record) => $record && $record->user_id !== auth()->id()),
                Section::make('Note History')
                    ->schema([
                        Repeater::make('logs')
                            ->label('List Notes')
                            ->relationship()
                            ->schema([
                                Grid::make()
                                    ->schema([
                                        TextInput::make('user_id')
                                            ->label('Approver')
                                            ->formatStateUsing(fn($state) => User::find($state)?->name ?? '-')
                                            ->disabled(),

                                        TextInput::make('action')
                                            ->label('Action')
                                            ->formatStateUsing(fn($state) => ucfirst($state))
                                            ->disabled(),

                                        TextInput::make('created_at')
                                            ->label('Date Time')
                                            ->formatStateUsing(fn($state) => \Carbon\Carbon::parse($state)->translatedFormat('d F Y, H:i') ?? '-')
                                            ->columnSpan(1)
                                            ->disabled(),

                                        Textarea::make('notes')
                                            ->label('Note')
                                            ->columnSpan([
                                                'sm' => 2,
                                                'xl' => 3,
                                                '2xl' => 4,
                                            ])
                                            ->disabled(),
                                    ])

                            ])
                            ->itemLabel("Notes")
                            ->defaultItems(0)
                            ->disabled()
                            ->collapsible()
                            ->collapsed()

                    ])
                    ->hidden(fn($record) => $record?->logs->isEmpty() ?? true)
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('user.name')
                    ->label('Requestor')
                    ->weight(FontWeight::Bold)
                    ->searchable()
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('flow.name')
                    ->label('Flow Name')
                    ->badge()
                    ->color('primary')
                    ->toggleable(),
                TextColumn::make('description')
                    ->label('Description')
                    ->limit(50)
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('created_at')
                    ->label('Submitted At')
                    ->since()
                    ->dateTimeTooltip()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->label('Last Updated')
                    ->since()
                    ->dateTimeTooltip()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->searchable()
                    ->formatStateUsing(fn(string $state): string => match ($state) {
                        'pending' => 'Pending Approval',
                        'approved' => 'Approved',
                        'onHold' => 'On Hold',
                        'rejected' => 'Rejected',
                        default => $state,
                    })
                    ->color(fn(string $state): string => match ($state) {
                        'pending' => 'warning',
                        'onHold' => 'warning',
                        'approved' => 'success',
                        'rejected' => 'danger',
                        default => 'gray',
                    })
                    ->icon(fn(string $state): string => match ($state) {
                        'pending' => 'heroicon-o-clock',
                        'onHold' => 'heroicon-o-exclamation-circle',
                        'approved' => 'heroicon-o-check-badge',
                        'rejected' => 'heroicon-o-x-circle',
                        default => 'heroicon-o-question-mark-circle',
                    })
                    ->toggleable(),

                TextColumn::make('current_level')
                    ->label('Step')
                    ->formatStateUsing(
                        fn($state, $record) =>
                        $record->status === 'approved'
                            ? "{$state} : Completed"
                            : ($record->status === 'rejected'
                                ? "{$state} : Rejected"
                                : (optional($record->flow)->steps ? "Step {$state}/" . $record->flow->steps->count() : "Deleted")
                            )
                    )
                    ->badge()
                    ->color(
                        fn($state, $record) =>
                        $record->status === 'approved' ? 'success' : ($record->status === 'rejected' ? 'danger' : (optional($record->flow)->steps && $state >= $record->flow->steps->count() ? 'success' : 'primary'))
                    )
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('logs')
                    ->label('Reviewers')
                    ->formatStateUsing(function ($record) {
                        $approvers = $record->logs
                            ->whereIn('action', ['approved', 'rejected'])
                            ->map(fn($log) => $log->user->name)
                            ->unique()
                            ->join(', ');

                        return $approvers ?: '-';
                    })
                    ->html()
                    ->tooltip('People who have reviewed')
                    ->color(function ($record) {
                        $lastLog = $record->logs->last();
                        return match ($lastLog?->action) {
                            // 'approved' => 'success',
                            // 'rejected' => 'danger',
                            default => 'secondary',
                        };
                    })
                    ->searchable(
                        query: fn(Builder $query, string $search) =>
                        $query->whereHas(
                            'logs.user',
                            fn($q) =>
                            $q->where('name', 'like', "%$search%")
                        )
                    )

            ])->modifyQueryUsing(fn(Builder $query) => $query->with(['logs.user']))
            ->poll('5s')
            ->defaultSort('created_at', 'desc')
            ->filters([
                SelectFilter::make('status')
                    ->options([
                        'pending' => 'Pending',
                        'approved' => 'Approved',
                        'rejected' => 'Rejected',
                    ])
                    ->label('Status'),
                SelectFilter::make('logs.user_id')
                    ->relationship('logs.user', 'name')
                    ->searchable()
                    ->preload()
                    ->label('Approver'),
                Filter::make('created_at')
                    ->indicator('Date Range')
                    ->form([
                        DatePicker::make('created_from')
                            ->label('Created from'),
                        DatePicker::make('created_until')
                            ->label('Created until'),
                    ])->columns(4)->columnSpanFull()
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['created_from'],
                                fn(Builder $query, $date): Builder => $query->whereDate('created_at', '>=', $date),
                            )
                            ->when(
                                $data['created_until'],
                                fn(Builder $query, $date): Builder => $query->whereDate('created_at', '<=', $date),
                            );
                    })
                    ->indicateUsing(function (array $data): array {
                        $indicators = [];

                        if ($data['created_from'] ?? null) {
                            $indicators[] = Indicator::make('Created from ' . Carbon::parse($data['created_from'])->toFormattedDateString())
                                ->removeField('created_from');
                        }

                        if ($data['created_until'] ?? null) {
                            $indicators[] = Indicator::make('Created until ' . Carbon::parse($data['created_until'])->toFormattedDateString())
                                ->removeField('created_until');
                        }

                        return $indicators;
                    }),
            ], layout: FiltersLayout::AboveContentCollapsible)

            ->actions([
                ActionGroup::make([
                    Action::make('approve')
                        ->label('Approve')
                        ->color('success')
                        ->icon('heroicon-o-check-circle')
                        ->form([
                            Textarea::make('notes')
                                ->label('Approval Note')
                                ->placeholder('Add Note (opsional)')
                        ])
                        ->action(function (ApprovalRequest $record, array $data) {
                            $record->approve($data['notes'] ?? '');
                        })
                        ->visible(fn(ApprovalRequest $record): bool => $record->isApprovalPending() && auth()->user()->canApprove($record)),

                    Action::make('onHold')
                        ->label('Hold')
                        ->color('warning')
                        ->icon('heroicon-o-clock')
                        ->form([
                            Textarea::make('notes')
                                ->label('Hold Note')
                                ->placeholder('Hold Message')
                                ->required()
                        ])
                        ->action(function (ApprovalRequest $record, array $data) {
                            $record->onHold($data['notes']);
                        })
                        ->visible(fn(ApprovalRequest $record): bool => $record->isApprovalPending() && auth()->user()->canApprove($record)),
                    Action::make('reject')
                        ->color('danger')
                        ->icon('heroicon-o-x-circle')
                        ->form([
                            Textarea::make('notes')
                                ->label('Rejection Note')
                                ->placeholder('Rejection Message')
                                ->required() // Bisa dibuat required untuk penolakan
                        ])
                        ->action(function (ApprovalRequest $record, array $data) {
                            $record->reject($data['notes']);
                        })
                        ->visible(fn(ApprovalRequest $record): bool => $record->isApprovalPending() && auth()->user()->canApprove($record)),
                ])->button()->label('Actions')->icon('heroicon-m-ellipsis-horizontal'),
                EditAction::make(),

            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    BulkAction::make('exportPDF')
                        ->label('Download PDF')
                        ->icon('heroicon-o-folder-arrow-down')
                        ->deselectRecordsAfterCompletion()
                        ->action(fn(Collection $records) => redirect()->away(
                            URL::signedRoute('report.approval', [
                                'ids' => $records->pluck('id')->toArray()
                            ])

                        )),
                ]),
            ]);
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->with(['flow.steps', 'user', 'logs.user']) // Eager loading
            ->where(function ($query) {
                $user = auth()->user();

                // Jika user adalah Admin, bisa melihat semua data
                if ($user->hasRole('Admin')) {
                    return; // Mengembalikan semua data tanpa filter tambahan
                }

                // Jika bukan Admin, hanya bisa melihat data yang terkait dengannya
                $query->where('user_id', $user->id)

                    // ATAU request yang perlu disetujui oleh user ini (termasuk onHold)
                    ->orWhere(function ($query) use ($user) {
                        $query->whereIn('status', ['pending', 'onHold']) // Memasukkan status 'onHold'
                            ->whereHas('flow.steps', function ($q) use ($user) {
                                // Cari step yang level-nya sesuai dengan current_level
                                // dan user_id-nya sesuai dengan yang harus menyetujui
                                $q->where('user_id', $user->id)
                                    ->whereColumn(
                                        'approval_flow_steps.level',
                                        'approval_requests.current_level'
                                    );
                            });
                    })

                    // ATAU request yang pernah diproses oleh user ini
                    ->orWhere(function ($query) use ($user) {
                        $query->whereHas('logs', function ($q) use ($user) {
                            $q->where('user_id', $user->id);
                        });
                    });
            });
    }

    public static function getNavigationBadge(): ?string
    {
        $user = auth()->user();

        if ($user->hasRole('Admin')) {
            // Jika Admin, hitung jumlah pending dan total untuk semua data
            $total = static::getModel()::count(); // Jumlah total
            $pending = static::getModel()::where('status', 'pending')->count(); // Jumlah pending
            return "$pending / $total"; // Format "pending / total"
        }

        // Jika User Biasa, hitung jumlah total dan pending milik mereka
        $totalUser = static::getModel()::where('user_id', $user->id)->count();
        $pendingUser = static::getModel()::where('user_id', $user->id)
            ->where('status', 'pending')
            ->count();

        // Hitung request yang menunggu persetujuan user ini (termasuk onHold)
        $pendingToApprove = static::getModel()::whereIn('status', ['pending', 'onHold'])
            ->whereHas('flow.steps', function ($query) use ($user) {
                $query->where('user_id', $user->id)
                    ->whereColumn(
                        'approval_flow_steps.level',
                        'approval_requests.current_level'
                    );
            })->count();

        // Hitung total request yang perlu diproses oleh approver
        $totalToApprove = static::getModel()::whereHas('flow.steps', function ($query) use ($user) {
            $query->where('user_id', $user->id);
        })->count();

        // Jika sudah di-approve atau ditolak oleh approver, badge tetap menampilkan 0/1 atau jumlah yang sesuai
        $total = $totalUser + $totalToApprove;
        $pending = $pendingUser + $pendingToApprove;

        return "$pending / $total"; // Format "pending / total"
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
            'index' => Pages\ListApprovalRequests::route('/'),
            'create' => Pages\CreateApprovalRequest::route('/create'),
            // 'edit' => Pages\EditApprovalRequest::route('/{record}/edit'),
        ];
    }
}
