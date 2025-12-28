<?php

namespace App\Http\Controllers;

use App\Domains\Sales\Models\Order;
use Illuminate\Http\Request;

class CheckoutController extends Controller
{
    /**
     * Mostrar página de checkout.
     */
    public function index()
    {
        return view('checkout.index');
    }
    
    /**
     * Mostrar página de procesamiento (esperando confirmación del webhook).
     */
    public function processing(Order $order)
    {
        // Verificar que la orden existe y está pendiente
        if ($order->status !== \App\Domains\Sales\States\OrderStatus::PENDING) {
            // Si ya fue confirmada, redirigir a éxito
            return redirect()->route('checkout.success', $order->id);
        }
        
        $order->load(['items.product', 'customer']);
        return view('checkout.processing', compact('order'));
    }
    
    /**
     * Mostrar página de éxito después de la orden.
     */
    public function success(Order $order)
    {
        // Verificar que la orden existe y está pagada
        if ($order->status !== \App\Domains\Sales\States\OrderStatus::PAID) {
            // Si aún está pendiente, redirigir a procesamiento
            return redirect()->route('checkout.processing', $order->id);
        }
        
        $order->load(['items.product', 'customer']);
        return view('checkout.success', compact('order'));
    }
}

