<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CompraResource\Pages;
use App\Filament\Resources\CompraResource\RelationManagers;
use App\Models\Compra;
use Filament\Forms;
use Filament\Forms\Components\Group;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\ToggleButtons;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

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
                            ->createOptionForm([]),

                        Textarea::make('notes')
                            ->label('Notas')
                            ->placeholder('Ingrese cualquier nota con respecto a la compra')
                            ->rows(5)
                            ->nullable()
                            ->columnSpanFull(),
                    ])->columns(2),
                ])->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                //
            ])
            ->filters([
                Tables\Filters\TrashedFilter::make(),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
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
