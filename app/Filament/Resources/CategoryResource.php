<?php

namespace App\Filament\Resources;

use App\Domains\Catalog\Models\Category;
use App\Filament\Resources\CategoryResource\Pages;
use Filament\Actions;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Str;

class CategoryResource extends Resource
{
    protected static ?string $model = Category::class;
    
    public static function getNavigationIcon(): ?string
    {
        return 'heroicon-o-tag';
    }
    
    public static function getNavigationGroup(): ?string
    {
        return 'Catálogo';
    }
    
    public static function getNavigationSort(): ?int
    {
        return 2;
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->required()
                    ->maxLength(255)
                    ->live(onBlur: true)
                    ->afterStateUpdated(fn ($state, callable $set) => $set('slug', Str::slug($state))),
                
                Forms\Components\TextInput::make('slug')
                    ->maxLength(255)
                    ->unique(ignoreRecord: true),
                
                Forms\Components\Textarea::make('description')
                    ->rows(3)
                    ->columnSpanFull(),
                
                Forms\Components\Select::make('parent_id')
                    ->label('Categoría Padre')
                    ->relationship('parent', 'name')
                    ->searchable()
                    ->preload()
                    ->placeholder('Sin categoría padre (Nivel raíz)')
                    ->helperText('Selecciona una categoría padre para crear una subcategoría'),
                
                Forms\Components\Toggle::make('is_active')
                    ->label('Activa')
                    ->default(true),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable()
                    ->weight('medium'),
                
                Tables\Columns\TextColumn::make('slug')
                    ->searchable()
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('parent.name')
                    ->label('Categoría Padre')
                    ->sortable()
                    ->badge()
                    ->placeholder('Raíz')
                    ->default('Raíz'),
                
                Tables\Columns\TextColumn::make('children_count')
                    ->counts('children')
                    ->label('Subcategorías')
                    ->badge()
                    ->color('info'),
                
                Tables\Columns\TextColumn::make('products_count')
                    ->counts('products')
                    ->label('Productos')
                    ->badge(),
                
                Tables\Columns\IconColumn::make('is_active')
                    ->boolean()
                    ->label('Activa'),
                
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Estado')
                    ->placeholder('Todas')
                    ->trueLabel('Solo activas')
                    ->falseLabel('Solo inactivas'),
                
                Tables\Filters\SelectFilter::make('parent_id')
                    ->label('Categoría Padre')
                    ->relationship('parent', 'name')
                    ->searchable()
                    ->preload()
                    ->placeholder('Todas'),
                
                Tables\Filters\Filter::make('root_categories')
                    ->label('Solo Categorías Raíz')
                    ->query(fn ($query) => $query->whereNull('parent_id')),
            ])
            ->actions([
                Actions\EditAction::make(),
                Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Actions\BulkActionGroup::make([
                    Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('name');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCategories::route('/'),
            'create' => Pages\CreateCategory::route('/create'),
            'edit' => Pages\EditCategory::route('/{record}/edit'),
        ];
    }
}

