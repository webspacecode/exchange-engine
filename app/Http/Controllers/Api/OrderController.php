<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

use App\Models\Order;
use App\Models\Asset;
use App\Models\User;

class OrderController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'symbol' => 'required|in:BTC,ETH',
            'side'   => 'required|in:buy,sell',
            'price'  => 'required|numeric|min:0.00000001',
            'amount' => 'required|numeric|min:0.00000001',
        ]);

        $userId = auth()->id();

        DB::transaction(function () use ($request, $userId) {
            $user = User::where('id', $userId)
                ->lockForUpdate()
                ->first();

            if ($request->side === 'buy') {
                $totalCost = bcmul($request->price, $request->amount, 8);

                if (bccomp($user->balance, $totalCost, 8) < 0) {
                    abort(422, 'Insufficient USD balance');
                }

                $user->balance = bcsub($user->balance, $totalCost, 8);
                $user->save();
            } else {
                $asset = Asset::where('user_id', $user->id)
                    ->where('symbol', $request->symbol)
                    ->lockForUpdate()
                    ->first();
                if (!$asset || bccomp($asset->amount, $request->amount, 8) < 0) {
                    abort(422, 'Insufficient asset balance');
                }
                $asset->amount = bcsub($asset->amount, $request->amount, 8);
                $asset->locked_amount = bcadd($asset->locked_amount, $request->amount, 8);
                $asset->save();
            }

            Order::create([
                'user_id' => $user->id,
                'symbol'  => $request->symbol,
                'side'    => $request->side,
                'price'   => $request->price,
                'amount'  => $request->amount,
                'status'  => Order::OPEN_ORDER,
            ]);
        });

        return response()->json([
            'message' => 'Order placed successfully'
        ], 201);
    }
}
