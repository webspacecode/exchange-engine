<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

use App\Models\User;
use App\Models\Asset;
use App\Models\Order;
use Illuminate\Support\Facades\DB;

class DemoDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = [
            [
                'name' => 'John Cena',
                'email' => 'john@example.com',
                'password' => bcrypt('password'),
                'balance' => 120000
            ],
            [
                'name' => 'Alex Wang',
                'email' => 'alex@example.com',
                'password' => bcrypt('password'),
                'balance' => 150000
            ]
        ];

        foreach ($users as $userData) {
            $user = User::firstOrCreate(
                ['email' => $userData['email']],
                $userData
            );

            $assets = ['BTC' => 2, 'ETH' => 5];
            
            // Assign asset to each user
            foreach ($assets as $symbol => $amount) {
                Asset::firstOrCreate(
                    ['user_id' => $user->id, 'symbol' => $symbol],
                    ['amount' => $amount, 'locked_amount' => 0]
                );
            }
        }

        DB::transaction(function () {
            // For BTC starts here
            $buyBtc = Order::firstOrCreate(
                [
                    'user_id' => 1,
                    'symbol' => 'BTC',
                    'side' => 'buy',
                    'price' => 60000,
                    'amount' => 0.1,
                    'status' => 1
                ]
            );

            $buyer = User::find(1);
            $buyer->balance -= $buyBtc->price * $buyBtc->amount;
            $buyer->save();

            $sellBtc = Order::firstOrCreate(
                [
                    'user_id' => 2,
                    'symbol' => 'BTC',
                    'side' => 'sell',
                    'price' => 59000,
                    'amount' => 0.1,
                    'status' => 1
                ]
            );

            $sellerAsset = Asset::where('user_id', 2)->where('symbol', 'BTC')->first();
            $sellerAsset->amount -= $sellBtc->amount;
            $sellerAsset->locked_amount += $sellBtc->amount;
            $sellerAsset->save();

            // For BTC ends here

            // For ETH starts here
            $buyEth = Order::firstOrCreate(
                [
                    'user_id' => 1,
                    'symbol' => 'ETH',
                    'side' => 'buy',
                    'price' => 4000,
                    'amount' => 1,
                    'status' => 1
                ]
            );
            $buyer->balance -= $buyEth->price * $buyEth->amount;
            $buyer->save();

            $sellEth = Order::firstOrCreate(
                [
                    'user_id' => 2,
                    'symbol' => 'ETH',
                    'side' => 'sell',
                    'price' => 3900,
                    'amount' => 1,
                    'status' => 1
                ]
            );
            $sellerEth = Asset::where('user_id', 2)->where('symbol', 'ETH')->first();
            $sellerEth->amount -= $sellEth->amount;
            $sellerEth->locked_amount += $sellEth->amount;
            $sellerEth->save();
            // For ETH ends here
        });
    }
}
