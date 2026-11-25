<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTicketsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tickets', function (Blueprint $table) {
            $table->increments('id')->unique();
            $table->integer('state_id')->unsigned();
            $table->integer('tip')->unsigned();
            $table->integer('subtotal')->unsigned();
            $table->integer('total')->unsigned();
            $table->timestamps();
        });

        Schema::table('products', function(Blueprint $table) {
           $table->foreign('category_id')->references('id')->on('categories')->onDelete('cascade')->onUpdate('cascade');
       });

        Schema::table('orders', function(Blueprint $table) {
           $table->foreign('product_id')->references('id')->on('products')->onDelete('cascade')->onUpdate('cascade');
           $table->foreign('state_id')->references('id')->on('states')->onDelete('cascade')->onUpdate('cascade');
       });

        Schema::table('tickets', function(Blueprint $table) {
           $table->foreign('state_id')->references('id')->on('states')->onDelete('cascade')->onUpdate('cascade');
       });

        Schema::table('states', function(Blueprint $table) {
           $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade')->onUpdate('cascade');
       });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('tickets');
    }
}
