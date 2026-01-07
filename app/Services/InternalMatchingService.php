<?php

namespace App\Services;

use App\Models\Order;
use App\Models\Asset;
use App\Models\Trade;
use App\Events\OrderMatched;
use Illuminate\Support\Facades\DB;

class InternalMatchingService
{
    public function match(Order $newOrder)
    {
        DB::transaction(function () use ($newOrder) {
            $newOrder->refresh();
            $newOrder->lockForUpdate();

            if ($newOrder->status !== 1) {
                return;
            }

            $counterOrder = $this->findCounterOrder($newOrder);

            if (!$counterOrder) {
                return;
            }

            // If both orders amount matched fully
            if (bccomp($counterOrder->amount, $newOrder->amount, 8) !== 0) {
                return;
            }

            $this->executeMatch($newOrder, $counterOrder);
        });
    }

    public function findCounterOrder(Order $order)
    {
        if ($order->side === 'buy') {
            return Order::where('symbol', $order->symbol)
                ->where('side', 'sell')
                ->where('status', 1)
                ->where('price', '<=', $order->price)
                ->orderBy('price')
                ->orderBy('created_at')
                ->lockForUpdate()
                ->first();
        }

        return Order::where('symbol', $order->symbol)
            ->where('side', 'buy')
            ->where('status', 1)
            ->where('price', '>=', $order->price)
            ->orderByDesc('price')
            ->orderBy('created_at')
            ->lockForUpdate()
            ->first();
    }

    private function executeMatch(Order $orderA, Order $orderB)
    {
        // Normalize roles
        $buyOrder  = $orderA->side === 'buy' ? $orderA : $orderB;
        $sellOrder = $orderA->side === 'sell' ? $orderA : $orderB;

        $amount = $buyOrder->amount;
        $price  = $sellOrder->price;

        $volume = bcmul($amount, $price, 8);

        $fee = bcmul($volume, '0.015', 8);

        $buyer = $buyOrder->user()->lockForUpdate()->first();
        $seller = $sellOrder->user()->lockForUpdate()->first();

        $buyerAsset = Asset::firstOrCreate(
            ['user_id' => $buyer->id, 'symbol' => $buyOrder->symbol],
            ['amount' => 0, 'locked_amount' => 0]
        );

        $buyerAsset->lockForUpdate();

        $sellerAsset = Asset::where('user_id', $seller->id)
            ->where('symbol', $sellOrder->symbol)
            ->lockForUpdate()
            ->first();

        // Buyer receive asset, pay fee
        $buyerAsset->amount = bcadd($buyerAsset->amount, $amount, 8);
        $buyerAsset->save();

        // Buyer fee deduction
        $buyer->balance = bcsub($buyer->balance, $fee, 8);
        $buyer->save();

        // Seller receive USD
        $seller->balance = bcadd($seller->balance, $volume, 8);
        $seller->save();

        // Seller asset unlock
        $sellerAsset->locked_amount = bcsub(
            $sellerAsset->locked_amount,
            $amount,
            8
        );
        $sellerAsset->save();

        $buyOrder->status  = 2;
        $sellOrder->status = 2;

        $buyOrder->save();
        $sellOrder->save();

        // Record Trade History
        Trade::create([
            'buy_order_id'  => $buyOrder->id,
            'sell_order_id' => $sellOrder->id,
            'buyer_id'      => $buyer->id,
            'seller_id'     => $seller->id,
            'symbol'        => $buyOrder->symbol,
            'price'         => $price,
            'amount'        => $amount,
            'volume'        => $volume,
            'fee'           => $fee,
        ]);
 
        broadcast(new OrderMatched(
            $buyOrder,
            $sellOrder,
            $price,
            $amount,
            $fee
        ));
    }
}
