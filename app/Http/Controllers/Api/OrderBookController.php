<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\Order;

class OrderBookController extends Controller
{
    public function index(Request $request)
    {
        $request->validate([
            'symbol' => 'required|in:BTC,ETH',
        ]);

        $symbol = $request->symbol;
        $userId = $request->user()->id;
    
        $openOrders = Order::where('symbol', $symbol)
            ->where('user_id', $userId)
            ->whereIn('side', ['buy', 'sell'])
            ->where('status', Order::OPEN_ORDER)
            ->orderBy('price', 'desc')
            ->orderBy('created_at', 'asc')
            ->limit(50)
            ->get(['id', 'side', 'price', 'amount', 'status', 'created_at']);

        $buyOrders = Order::where('symbol', $symbol)
            ->where('user_id', $userId)
            ->where('side', 'buy')
            ->whereIn('status', [Order::FILLED_ORDER, Order::CANCELLED_ORDER])
            ->orderBy('price', 'desc')
            ->orderBy('created_at', 'asc')
            ->limit(50)
            ->get(['price', 'amount', 'status', 'created_at']);

        $sellOrders = Order::where('symbol', $symbol)
            ->where('user_id', $userId)
            ->where('side', 'sell')
            ->whereIn('status', [Order::FILLED_ORDER, Order::CANCELLED_ORDER])
            ->orderBy('price', 'asc')
            ->orderBy('created_at', 'asc')
            ->limit(50)
            ->get(['price', 'amount', 'status', 'created_at']);

        return response()->json([
            'symbol' => $symbol,
            'buy'    => $buyOrders,
            'sell'   => $sellOrders,
            'openOrder' => $openOrders
        ]);
        
    }
}
