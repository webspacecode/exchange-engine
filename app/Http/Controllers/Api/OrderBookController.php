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
        $status = Order::OPEN_ORDER;

        $buyOrders = Order::where('symbol', $symbol)
            ->where('side', 'buy')
            ->where('status', $status)
            ->orderBy('price', 'desc')
            ->orderBy('created_at', 'asc')
            ->limit(3)
            ->get(['price', 'amount', 'created_at']);

        $sellOrders = Order::where('symbol', $symbol)
            ->where('side', 'sell')
            ->where('status', $status)
            ->orderBy('price', 'asc')
            ->orderBy('created_at', 'asc')
            ->limit(3)
            ->get(['price', 'amount', 'created_at']);

        return response()->json([
            'symbol' => $symbol,
            'buy'    => $buyOrders,
            'sell'   => $sellOrders,
        ]);
    }
}
