<?php

namespace App\Filament\Widgets;

use App\Domains\Sales\Models\Order;
use App\Domains\Sales\States\OrderStatus;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Number;

class SalesOverview extends BaseWidget
{
    /**
     * Polling interval para actualización en tiempo real (opcional).
     * Descomenta para actualizar cada 30 segundos.
     */
    // protected static ?string $pollingInterval = '30s';

    protected function getStats(): array
    {
        // Ventas de hoy (solo órdenes no canceladas)
        $todaySales = Order::whereDate('created_at', today())
            ->where('status', '!=', OrderStatus::CANCELLED)
            ->sum('total');
        
        // Ventas del mes actual
        $monthSales = Order::whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->where('status', '!=', OrderStatus::CANCELLED)
            ->sum('total');
        
        // Ventas del mes anterior (para comparación)
        $lastMonthSales = Order::whereMonth('created_at', now()->subMonth()->month)
            ->whereYear('created_at', now()->subMonth()->year)
            ->where('status', '!=', OrderStatus::CANCELLED)
            ->sum('total');
        
        // Cálculo de crecimiento porcentual
        $growthPercentage = $lastMonthSales > 0 
            ? (($monthSales - $lastMonthSales) / $lastMonthSales) * 100 
            : 0;
        
        // Total de órdenes completadas
        $totalOrders = Order::where('status', '!=', OrderStatus::CANCELLED)->count();
        
        // Órdenes pendientes
        $pendingOrders = Order::where('status', OrderStatus::PENDING)->count();
        
        // Órdenes pagadas (listas para enviar)
        $paidOrders = Order::where('status', OrderStatus::PAID)->count();
        
        // Promedio de venta por orden
        $averageOrderValue = Order::where('status', '!=', OrderStatus::CANCELLED)
            ->avg('total');
        
        return [
            Stat::make('Ventas de Hoy', Number::currency($todaySales, 'USD'))
                ->description('Ingresos del día de hoy')
                ->descriptionIcon('heroicon-m-arrow-trending-up')
                ->color('success')
                ->chart($this->getTodaySalesChart()),
            
            Stat::make('Ventas del Mes', Number::currency($monthSales, 'USD'))
                ->description(
                    $growthPercentage >= 0 
                        ? "↑ " . number_format(abs($growthPercentage), 1) . "% vs mes anterior"
                        : "↓ " . number_format(abs($growthPercentage), 1) . "% vs mes anterior"
                )
                ->descriptionIcon($growthPercentage >= 0 ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down')
                ->descriptionColor($growthPercentage >= 0 ? 'success' : 'danger')
                ->color('info'),
            
            Stat::make('Total de Órdenes', Number::format($totalOrders))
                ->description('Órdenes completadas')
                ->descriptionIcon('heroicon-m-shopping-cart')
                ->color('primary'),
            
            Stat::make('Órdenes Pendientes', Number::format($pendingOrders))
                ->description('Requieren atención')
                ->descriptionIcon('heroicon-m-clock')
                ->color('warning'),
            
            Stat::make('Órdenes Pagadas', Number::format($paidOrders))
                ->description('Listas para enviar')
                ->descriptionIcon('heroicon-m-check-circle')
                ->color('info'),
            
            Stat::make('Ticket Promedio', Number::currency($averageOrderValue ?? 0, 'USD'))
                ->description('Por orden')
                ->descriptionIcon('heroicon-m-calculator')
                ->color('success'),
        ];
    }

    /**
     * Obtener datos para el gráfico de ventas del día (últimas 12 horas).
     */
    protected function getTodaySalesChart(): array
    {
        $hours = [];
        $sales = [];
        
        for ($i = 11; $i >= 0; $i--) {
            $hour = now()->subHours($i)->format('H:00');
            $hourStart = now()->subHours($i)->startOfHour();
            $hourEnd = now()->subHours($i)->endOfHour();
            
            $hourSales = Order::whereBetween('created_at', [$hourStart, $hourEnd])
                ->where('status', '!=', OrderStatus::CANCELLED)
                ->sum('total');
            
            $hours[] = $hour;
            $sales[] = (float) $hourSales;
        }
        
        return $sales;
    }
}

