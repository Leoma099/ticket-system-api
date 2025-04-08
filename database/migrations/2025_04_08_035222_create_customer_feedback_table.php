<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCustomerFeedbackTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('customer_feedback', function (Blueprint $table) {
            $table->bigIncrements('id');

            $table->bigInteger('account_id')->nullable();
            $table->bigInteger('ticket_id')->nullable();

            $table->string('ticket_order');
            $table->string('full_name');
            $table->string('assigned_by');
            $table->date('completed_date');
            $table->time('completed_time');
            $table->tinyInteger('rate');
            $table->text('comment');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('customer_feedback');
    }
}
