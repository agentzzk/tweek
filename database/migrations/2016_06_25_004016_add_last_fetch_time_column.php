<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddLastFetchTimeColumn extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
     public function up()
     {
         Schema::table('subs', function ($table) {
             $table->dateTimeTz('last_API_fetch')->nullable();
         });
     }

     /**
      * Reverse the migrations.
      *
      * @return void
      */
     public function down()
     {
         Schema::table('subs', function ($table) {
             $table->dropColumn('last_API_fetch');
         });
     }
}
