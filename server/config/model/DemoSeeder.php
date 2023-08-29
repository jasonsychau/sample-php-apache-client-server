<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Account;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class DemoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
      Account::factory()
              ->count(301)
              ->create();
      $currencies = 2;
      DB::table('currencys')->insert([
         ['abbreviation'=>'CAD'],
         ['abbreviation'=>'USD'],
      ]);
      $products = 3;
      DB::table('items')->insert([
        ['name'=>'Logitech MX Keys'],
        ['name'=>'Logitech MX Master'],
        ['name'=>'Logitech Z906'],
      ]);
      $follows = array_map(function($i) { return [
          'follower_id' => $i,
          'subject_id' => $i+1,
          'followed_at' => $this->random_date(
            mktime(date("h"), date("i"), date("s"), date("m")-3, date("d"), date("Y")),
            now()
          ),
      ];}, range(1, 300));
      DB::table('followers')->insert($follows);
      DB::table('followers')->insert([
        'follower_id' => 301,
        'subject_id' => 1,
        'followed_at' => $this->random_date(
            mktime(date("h"), date("i"), date("s"), date("m")-3, date("d"), date("Y")),
            now()
          ),
      ]);
      $subscribes = array_map(function($i) { return [
          'subscriber_id' => $i,
          'subscription_id' => $i+1,
          'tier' => rand(1,3),
          'subscribed_at' => $this->random_date(
            mktime(date("h"), date("i"), date("s"), date("m")-3, date("d"), date("Y")),
            now()
          ),
      ];}, range(1, 300));
      DB::table('subscribers')->insert($subscribes);
      DB::table('subscribers')->insert([
        'subscriber_id' => 301,
        'subscription_id' => 1,
        'tier' => rand(1,3),
        'subscribed_at' => $this->random_date(
            mktime(date("h"), date("i"), date("s"), date("m")-3, date("d"), date("Y")),
            now()
          ),
      ]);
      $donations = array_map(function($i) use ($currencies) { return [
          'donor_id' => $i,
          'fund_id' => $i+1,
          'donated_at' => $this->random_date(
            mktime(date("h"), date("i"), date("s"), date("m")-3, date("d"), date("Y")),
            now()
          ),
          'amount' => ((float)rand(1,100000000000)) / 100,
          'currency_id' => rand(1,$currencies),
          'message' => Str::random(20),
      ];}, range(1, 300));
      DB::table('donations')->insert($donations);
      DB::table('donations')->insert([
        'donor_id' => 301,
        'fund_id' => 1,
        'donated_at' => $this->random_date(
            mktime(date("h"), date("i"), date("s"), date("m")-3, date("d"), date("Y")),
            now()
          ),
        'amount' => ((float)rand(1,100000000000)) / 100,
        'currency_id' => rand(1,$currencies),
        'message' => Str::random(20),
      ]);
      $purchases = array_map(function($i) use ($currencies, $products) { return [
          'merchant_id' => $i,
          'customer_id' => $i+1,
          'purchased_at' => $this->random_date(
            mktime(date("h"), date("i"), date("s"), date("m")-3, date("d"), date("Y")),
            now()
          ),
          'price' => ((float)rand(1,100000000000)) / 100,
          'currency_id' => rand(1,$currencies),
          'amount' => rand(1,100),
          'item_id' => rand(1, $products),
      ];}, range(1, 300));
      DB::table('merch_sales')->insert($purchases);
      DB::table('merch_sales')->insert([
        'merchant_id' => 301,
        'customer_id' => 1,
        'purchased_at' => $this->random_date(
            mktime(date("h"), date("i"), date("s"), date("m")-3, date("d"), date("Y")),
            now()
          ),
        'price' => ((float)rand(1,100000000000)) / 100,
        'currency_id' => rand(1,$currencies),
        'amount' => rand(1,100),
        'item_id' => rand(1, $products),
      ]);
    }

    private function random_date(string $min_date, string $max_date): string 
    {
      $min_epoch = strtotime($min_date);
      $max_epoch = strtotime($max_date);

      $rand_epoch = rand($min_epoch, $max_epoch);

      return date('Y-m-d h:i:s', $rand_epoch);
    }
}
