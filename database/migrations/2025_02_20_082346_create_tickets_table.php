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
            $table->bigInteger('account_id')->nullable();  // Nullable for walk-ins
            $table->string('full_name');  // For walk-in users
            $table->string('school_number');  // School identifier
            $table->tinyInteger('subject')->default(0);  // Subject of the ticket
            $table->tinyInteger('department')->default(0);  // Department handling the ticket
            $table->tinyInteger('priority_level')->default(1);  // Priority (low=1, medium=2, high=3)
            $table->tinyInteger('status')->default(1);  // Status (Pending=1, In-Progress=2, etc.)
            $table->longText('photo')->nullable();  // Optional photo (if applicable)
            $table->text('description')->nullable();  // Description of the issue
            $table->date('request_date')->useCurrent();  // Automatically set to now
            $table->date('completed_date')->nullable();  // Completed date (nullable)
            $table->tinyInteger('assigned_by')->default(0);  // ID of the admin/staff member assigning the ticket
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
