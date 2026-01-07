<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Trade;
use Illuminate\Support\Facades\Auth;

class TradeController extends Controller
{
    public function userTrades(Request $request)
    {
        $userId = Auth::id();

        $trades = Trade::where('buyer_id', $userId)
            ->orWhere('seller_id', $userId)
            ->orderBy('created_at', 'desc')
            ->limit(50)
            ->get(['symbol', 'buyer_id', 'seller_id', 'price', 'amount', 'created_at']);

        $formatted = $trades->map(function ($trade) use ($userId) {
            return [
                'pair' => $trade->symbol . '/USD',
                'side' => $trade->buyer_id === $userId ? 'Buy' : 'Sell',
                'price' => $trade->price,
                'amount' => $trade->amount,
                'time' => $trade->created_at->diffForHumans(),
            ];
        });

        return response()->json([
            'success' => true,
            'data' => $formatted
        ]);
    }
}
