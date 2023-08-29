<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('accounts', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name', 100);
            $table->string('email')->nullable();
        });
        Schema::create('currencys', function (Blueprint $table) {
            $table->increments('id');
            $table->string('abbreviation', 3);
        });
        Schema::create('items', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name', 100);
        });
        Schema::create('followers', function (Blueprint $table) {
            $table->unsignedInteger('subject_id');
            $table->unsignedInteger('follower_id');
            $table->foreign('subject_id')->references('id')->on('accounts')->onDelete('cascade');
            $table->foreign('follower_id')->references('id')->on('accounts')->onDelete('cascade');
            $table->timestamp('followed_at');
            $table->boolean('read')->default(false);
        });
        Schema::create('subscribers', function (Blueprint $table) {
            $table->unsignedInteger('subscriber_id');
            $table->unsignedInteger('subscription_id');
            $table->foreign('subscriber_id')->references('id')->on('accounts')->onDelete('cascade');
            $table->foreign('subscription_id')->references('id')->on('accounts')->onDelete('cascade');
            $table->timestamp('subscribed_at');
            $table->unsignedInteger('tier');
            $table->boolean('read')->default(false);
        });
        Schema::create('donations', function (Blueprint $table) {
            $table->unsignedInteger('donor_id');
            $table->unsignedInteger('fund_id');
            $table->foreign('donor_id')->references('id')->on('accounts')->onDelete('cascade');
            $table->foreign('fund_id')->references('id')->on('accounts')->onDelete('cascade');
            $table->timestamp('donated_at');
            $table->float('amount', 12, 2);
            $table->unsignedInteger('currency_id');
            $table->foreign('currency_id')->references('id')->on('currencys')->onDelete('cascade');
            $table->longText('message');
            $table->boolean('read')->default(false);
        });
        Schema::create('merch_sales', function (Blueprint $table) {
            $table->unsignedInteger('merchant_id');
            $table->unsignedInteger('customer_id');
            $table->unsignedInteger('item_id')->unsigned();
            $table->foreign('merchant_id')->references('id')->on('accounts')->onDelete('cascade');
            $table->foreign('customer_id')->references('id')->on('accounts')->onDelete('cascade');
            $table->foreign('item_id')->references('id')->on('items')->onDelete('cascade');
            $table->timestamp('purchased_at');
            $table->integer('amount')->unsigned();
            $table->float('price', 12, 2);
            $table->unsignedInteger('currency_id');
            $table->foreign('currency_id')->references('id')->on('accounts')->onDelete('cascade');
            $table->boolean('read')->default(false);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('accounts');
        Schema::dropIfExists('currencys');
        Schema::dropIfExists('followers');
        Schema::dropIfExists('subscribers');
        Schema::dropIfExists('donations');
        Schema::dropIfExists('merch_sales');
    }
};
