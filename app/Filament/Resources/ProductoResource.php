<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProductoResource\Pages;
use App\Filament\Resources\ProductoResource\RelationManagers;
use App\Models\Categoria;
use App\Models\Marca;
use App\Models\Producto;
use Filament\Forms;
use Filament\Forms\Components\Group;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ProductoResource extends Resource
{
    protected static ?string $model = Producto::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Group::make()->schema([
                    Section::make('Información del producto')->schema([
                        TextInput::make('sku')
                            ->label('Codigo de referencia')
                            ->placeholder('Ingrese el codigo del producto')
                            ->autofocus()
                            ->numeric()
                            ->minValue(0)
                            ->required()
                            ->unique(Producto::class, 'sku', ignoreRecord: true),

                        TextInput::make('nombre')
                            ->placeholder('Ingrese el nombre del producto')
                            ->maxLength(255)
                            ->required()
                            ->unique(Producto::class, 'nombre', ignoreRecord: true)
                            ->columnSpan(2),

                        Textarea::make('descripcion')
                            ->placeholder('Ingrese la descripción del producto')
                            ->nullable()
                            ->rows(6)
                            ->columnSpanFull(),
                    ])->columns(3),
                ])->columnSpan(2),

                Group::make()->schema([
                    Section::make('Precio e Inventario')->schema([
                        TextInput::make('precio')
                            ->placeholder('00.00')
                            ->numeric()
                            ->prefix('$')
                            ->required(),

                        TextInput::make('stock')
                            ->label('Existencias')
                            ->required()
                            ->numeric()
                            ->minValue(0)
                            ->default(0),
                    ]),

                    Section::make('Asociaciones')->schema([
                        Select::make('categoria_id')
                            ->label('Categoría')
                            ->placeholder('Selecciona una categoría')
                            ->relationship('categoria', 'nombre')
                            ->required()
                            ->preload()
                            ->searchable()
                            ->createOptionForm([
                                TextInput::make('nombre')
                                    ->placeholder('Ingrese el nombre de la categoria')
                                    ->required()
                                    ->maxLength(191)
                                    ->unique(Categoria::class, 'nombre', ignoreRecord: true),
                            ]),

                        Select::make('marca_id')
                            ->label('Marca')
                            ->placeholder('Selecciona una marca')
                            ->relationship('marca', 'nombre')
                            ->required()
                            ->preload()
                            ->searchable()
                            ->createOptionForm([
                                TextInput::make('nombre')
                                    ->placeholder('Ingrese el nombre de la marca')
                                    ->required()
                                    ->maxLength(191)
                                    ->unique(Marca::class, 'nombre', ignoreRecord: true),
                            ]),
                    ]),

                ])->columnSpan(1),
            ])->columns(3);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('sku')
                    ->label('Codigo'),

                TextColumn::make('nombre')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('precio')
                    ->prefix('$')
                    ->sortable(),

                TextColumn::make('stock')
                    ->numeric()
                    ->sortable()
                    ->searchable(),

                TextColumn::make('categoria.nombre')
                    ->searchable()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('marca.nombre')
                    ->searchable()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('created_at')
                    ->label('Fecha de registro')
                    ->dateTime('d-m-Y H:m:s')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('updated_at')
                    ->label('Fecha de actualización')
                    ->dateTime('d-m-Y H:m:s')
                    ->searchable()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('categoria')
                    ->label('Categoría')
                    ->placeholder('Todas')
                    ->relationship('categoria', 'nombre'),

                SelectFilter::make('marca')
                    ->label('Marca')
                    ->placeholder('Todas')
                    ->relationship('marca', 'nombre'),
            ])
            ->actions([
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\ViewAction::make(),
                    Tables\Actions\EditAction::make(),
                    Tables\Actions\DeleteAction::make(),
                ]),
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
            'index' => Pages\ListProductos::route('/'),
            'create' => Pages\CreateProducto::route('/create'),
            'edit' => Pages\EditProducto::route('/{record}/edit'),
        ];
    }
}
