<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/


Route::get('/', function (Request $request) {
    info($request->all());
    if (Arr::has($request->all(), 'code'))
    {
	info($request->all()['code']);
    }
    return view('welcome');
});

Route::get('/auth_config.json', function () {
    return File::get(public_path() . '/auth_config.json'); 
});

Route::post('/getdata', function (Request $request) {
    $email = $request->all()['email'];
    $registeredUsers = DB::table('accounts')->where('email', 'like', '$email')->get();

    function gatherData($accountId) {
        // get and aggregate data for user
        $follows = DB::select('select frs.name as follower_name, f.followed_at as time from accounts frs inner join followers f on frs.id=f.follower_id inner join accounts fld on f.subject_id=fld.id where fld.id=?', [$accountId]);
        $subs = DB::select('select sbr.name as subscriber_name, s.tier as tier, s.subscribed_at as time from accounts sbr inner join subscribers s on sbr.id=s.subscriber_id inner join accounts stn on s.subscription_id=stn.id where stn.id=?', [$accountId]);
        $donations = DB::select('select dnr.name as donor_name, d.amount as amount, d.message as message, c.abbreviation as currency, d.donated_at as time from accounts dnr inner join donations d on dnr.id=d.donor_id inner join accounts f on d.fund_id=f.id left join currencys c on d.currency_id=c.id where f.id=?', [$accountId]);
        $purchases = DB::select('select byr.name as buyer_name, ms.amount as count, ms.price as cost, c.abbreviation as currency, i.name as item, ms.purchased_at as time from accounts byr inner join merch_sales ms on byr.id=ms.customer_id inner join accounts mch on ms.merchant_id=mch.id left join items i on i.id = ms.item_id left join currencys c on c.id=ms.currency_id where mch.id=?', [$accountId]);
        function stringifyFollow($follow) {
            return array('message' => $follow->follower_name . " followed you!", 'time' => strtotime($follow->time));
        }
        $followsString = array_map('stringifyFollow', $follows);
        function stringifySub($sub) {
            return array(
                'message' => $sub->subscriber_name . " (Tier" . $sub->tier . ") subscribed to you!",
                'time' => strtotime($sub->time)
            );
        }
        $subsString = array_map('stringifySub', $subs);
        function stringifyDonations($donation) {
            return array(
                'message' => $donation->donor_name . " donated " . $donation->amount . " " . $donation->currency . " to you!",
                'time' => strtotime($donation->time)
            );
        }
        $donationsString = array_map('stringifyDonations', $donations);
        function stringifyPurchases($purchase) {
            return array(
                'message' => $purchase->buyer_name . " bought " . $purchase->count . " " . $purchase->item . "s from you for " . $purchase->cost . " " . $purchase->currency . "!",
                'time' => strtotime($purchase->time)
            );
        }
        $purchasesString = array_map('stringifyPurchases', $purchases);
        $allMessages = array_merge($followsString, $subsString, $donationsString, $purchasesString);
        $allMessages = collect($allMessages)->sortBy('time')->toArray();

        // calcuate revenue data
        $donationsTransactions = DB::select('select d.amount as amount, c.abbreviation as currency from donations d left join currencys c on d.currency_id=c.id where d.fund_id = ? and d.donated_at >= now() - interval 3 month', [$accountId]);
        $subsTransactions = DB::select('select tier from subscribers where subscription_id = ? and subscribed_at >= now() - interval 3 month', [$accountId]);
        $purchasesTransactions = DB::select('select ms.price as price, c.abbreviation as currency from merch_sales ms left join currencys c on ms.currency_id=c.id where merchant_id = ? and purchased_at >= now() - interval 3 month', [$accountId]);
        function sumDonations($carry, $donation) {
            if (array_key_exists($donation->currency, $carry)) {
                $carry[$donation->currency] = $carry[$donation->currency] + $donation->amount;
            } else {
                $carry[$donation->currency] = $donation->amount;
            }
            return $carry;
        }
        $donationAndPurchasesRevenue = array_reduce($donationsTransactions, "sumDonations", []);
        function sumSubs($carry, $sub) {
            return $carry + (5 * intval($sub->tier));
        }
        $subsRevenue = array_reduce($subsTransactions, "sumSubs", 0);
        function sumPurchases($carry, $purchases) {
            if (array_key_exists($purchases->currency, $carry)) {
                $carry[$purchases->currency] = $carry[$purchases->currency] + $purchases->price;
            } else {
                $carry[$purchases->currency] = $purchases->price;
            }
            return $carry;
        }
        $donationAndPurchasesRevenue = array_reduce($purchasesTransactions, "sumPurchases", $donationAndPurchasesRevenue);
        
        // followers
        $newFollowers = DB::select('select count(*) as count from followers where subject_id = ? and followed_at >= now() - interval 3 month', [$accountId]);

        // top 3 items by sales
        $topItems = DB::select('select sum(ms.amount) as count, min(i.name) as item_name from merch_sales ms left join items i on ms.item_id=i.id group by ms.item_id order by count desc limit 3');

        return [
            'notifications' => array_slice($allMessages, 0, 100),
            'revenue' => [
                'subs' => $subsRevenue,
                'donationsAndPurchases' => $donationAndPurchasesRevenue
            ],
            'topItems' => $topItems,
            'newFollowers' => $newFollowers[0]->count
        ];
    }

    if (count($registeredUsers) == 0) {
        // try to assign account to user
        $notAssignedDummyAccounts = DB::table('accounts')->whereNull('email')->get();
        if (count($notAssignedDummyAccounts) > 0) {
            $assignedAccount = $notAssignedDummyAccounts[0];
            DB::table('accounts')->where('id', $assignedAccount->id)->update(['email' => $email]);
            
            return gatherData($assignedAccount->id);
        } else {
            return [];
        }
    } else {
        $assignedAccount = $registeredUsers[0];
        return gatherData($assignedAccount->id);
    }
});
