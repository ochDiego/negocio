<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CompraResource\Pages;
use App\Filament\Resources\CompraResource\RelationManagers;
use App\Models\Cliente;
use App\Models\Compra;
use App\Models\Producto;
use Filament\Forms;
use Filament\Forms\Components\Group;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\ToggleButtons;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Number;

class CompraResource extends Resource
{
    protected static ?string $model = Compra::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Group::make()->schema([
                    Section::make('Información de la compra')->schema([
                        ToggleButtons::make('metodo_pago')
                            ->label('Metodo de pago')
                            ->options([
                                'Efectivo' => 'Efectivo',
                                'Transferencia' => 'Transferencia',
                                'Débito' => 'Débito',
                            ])
                            ->default('Efectivo')
                            ->inline()
                            ->required()
                            ->colors([
                                'Efectivo' => 'info',
                                'Transferencia' => 'info',
                                'Débito' => 'info',
                            ]),

                        ToggleButtons::make('estado_pago')
                            ->label('Estado de pago')
                            ->options([
                                'Pagado' => 'Pagado',
                                'Pendiente' => 'Pendiente',
                            ])
                            ->default('Pagado')
                            ->inline()
                            ->required()
                            ->colors([
                                'Pagado' => 'success',
                                'Pendiente' => 'warning',
                            ]),

                        Select::make('cliente_id')
                            ->label('Cliente')
                            ->nullable()
                            ->relationship('cliente', 'nombre')
                            ->preload()
                            ->searchable()
                            ->createOptionForm([
                                TextInput::make('nombre')
                                    ->label('Nombre y apellido')
                                    ->placeholder('Ingrese el nombre y apellido del cliente')
                                    ->maxLength(75)
                                    ->required()
                                    ->unique(Cliente::class, 'nombre', ignoreRecord: true),

                                TextInput::make('telefono')
                                    ->label('Teléfono')
                                    ->placeholder('cod de área sin el 0 ej: 2644929292')
                                    ->maxLength(10)
                                    ->nullable()
                                    ->prefix('+15')
                                    ->unique(Cliente::class, 'telefono', ignoreRecord: true),
                            ]),

                        Textarea::make('notes')
                            ->label('Notas')
                            ->placeholder('Ingrese cualquier nota con respecto a la compra')
                            ->rows(5)
                            ->nullable()
                            ->columnSpanFull(),
                    ])->columns(2),

                    Section::make('Productos de la compra')->schema([
                        Repeater::make('items')
                            ->label('Productos')
                            ->relationship()
                            ->schema([
                                Select::make('producto_id')
                                    ->relationship('producto', 'sku')
                                    ->label('Codigo')
                                    ->searchable()
                                    ->preload()
                                    ->required()
                                    ->distinct()
                                    ->disableOptionsWhenSelectedInSiblingRepeaterItems()
                                    ->columnSpan(2)
                                    ->reactive()
                                    ->afterStateUpdated(fn($state, Forms\Set $set) => $set('precio_unitario', Producto::find($state)?->precio ?? 0))
                                    ->afterStateUpdated(fn($state, Forms\Set $set) => $set('total', Producto::find($state)?->precio ?? 0))
                                    ->afterStateUpdated(fn($state, Forms\Set $set) => $set('nombre', Producto::find($state)?->nombre ?? '')),

                                TextInput::make('nombre')
                                    ->required()
                                    ->disabled()
                                    ->dehydrated()
                                    ->columnSpan(4),

                                TextInput::make('cantidad')
                                    ->numeric()
                                    ->required()
                                    ->default(1)
                                    ->minValue(1)
                                    ->columnSpan(2)
                                    ->reactive()
                                    ->afterStateUpdated(fn($state, Forms\Set $set, Forms\Get $get) => $set('total', $state * $get('precio_unitario'))),

                                TextInput::make('precio_unitario')
                                    ->numeric()
                                    ->prefix('$')
                                    ->required()
                                    ->disabled()
                                    ->dehydrated()
                                    ->columnSpan(2),

                                TextInput::make('total')
                                    ->numeric()
                                    ->prefix('$')
                                    ->required()
                                    ->disabled()
                                    ->dehydrated()
                                    ->columnSpan(2),
                            ])->columns(12),

                        Placeholder::make('gran_total')
                            ->label('Total a pagar')
                            ->content(function (Forms\Set $set, Forms\Get $get) {
                                $total = 0;
                                if (!$repeaters = $get('items')) {
                                    return $total;
                                }

                                foreach ($repeaters as $key => $repeater) {
                                    $total += $get("items.{$key}.total");
                                }
                                $set('gran_total', $total);
                                return Number::currency($total);
                            }),

                        Hidden::make('gran_total')
                            ->default(0),
                    ]),

                ])->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([

                TextColumn::make('gran_total')
                    ->label('Total')
                    ->numeric()
                    ->sortable()
                    ->prefix('$'),

                TextColumn::make('metodo_pago')
                    ->label('Metodo de pago')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('estado_pago')
                    ->label('Estado de pago')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('created_at')
                    ->label('Fecha y hora')
                    ->dateTime()
                    ->sortable()
                    ->searchable(),

                TextColumn::make('cliente.nombre')
                    ->searchable()
                    ->sortable(),
            ])
            ->filters([
                /*  Tables\Filters\TrashedFilter::make(), */])
            ->actions([
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\ViewAction::make(),
                    Tables\Actions\EditAction::make(),
                ]),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\ForceDeleteBulkAction::make(),
                    Tables\Actions\RestoreBulkAction::make(),
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
            'index' => Pages\ListCompras::route('/'),
            'create' => Pages\CreateCompra::route('/create'),
            'view' => Pages\ViewCompra::route('/{record}'),
            'edit' => Pages\EditCompra::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }
}
