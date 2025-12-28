<?php

namespace App\Filament\Widgets;

use App\Domains\Catalog\Models\Product;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class LowStockAlert extends BaseWidget
{
    protected int | string | array $columnSpan = 'full';
    
    protected static ?int $sort = 2;

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Product::query()
                    ->where('stock', '<', 10)
                    ->where('is_active', true)
                    ->orderBy('stock', 'asc')
            )
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable()
                    ->weight('medium'),
                
                Tables\Columns\TextColumn::make('sku')
                    ->searchable()
                    ->label('SKU'),
                
                Tables\Columns\TextColumn::make('category.name')
                    ->label('Categoría')
                    ->badge(),
                
                Tables\Columns\TextColumn::make('stock')
                    ->sortable()
                    ->badge()
                    ->color(fn ($state) => $state < 5 ? 'danger' : 'warning')
                    ->formatStateUsing(fn ($state) => "{$state} unidades"),
                
                Tables\Columns\TextColumn::make('price')
                    ->money('USD')
                    ->sortable(),
            ])
            ->heading('Productos con Stock Bajo')
            ->description('Productos con menos de 10 unidades en inventario')
            ->emptyStateHeading('No hay productos con stock bajo')
            ->emptyStateDescription('Todos los productos tienen suficiente inventario');
    }
}

