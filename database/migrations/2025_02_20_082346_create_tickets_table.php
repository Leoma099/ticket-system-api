<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

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
            $table->bigIncrements('id');
            $table->bigInteger('account_id')->nullable();
            $table->string('ticket_order');
            $table->string('full_name');  // For walk-in users
            $table->tinyInteger('subject');
            $table->tinyInteger('department');
            $table->tinyInteger('priority_level');
            $table->tinyInteger('status');
            $table->text('description')->nullable();  // Description of the issue
            $table->longText('photo')->nullable();  // Description of the issue
            $table->date('request_date')->useCurrent();  // Automatically set to now
            $table->date('completed_date')->nullable();  // Completed date (nullable)
            $table->time('completed_time')->nullable();  // Completed date (nullable)
            $table->tinyInteger('assigned_by');

            // Added 20250326
            $table->tinyInteger('approval_status')->default(1); // 1: FOR APPROVAL; 2: APPROVED; 3: CANCELED
            $table->bigInteger('approved_by')->nullable();
            $table->date('approved_date')->nullable();

            // Added 20250420
            $table->string('reason')->nullable();
            
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
        Schema::dropIfExists('tickets');
    }
}
