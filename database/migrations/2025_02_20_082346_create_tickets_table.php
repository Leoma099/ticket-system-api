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

            $table->string('full_name');
            $table->string('school_number');
            $table->tinyInteger('department'); 
            $table->tinyInteger('subject');
            $table->tinyInteger('priority_level'); // 1 = Low, 2 = Medium, 3 = High, 4 = Urgent
            $table->tinyInteger('status'); // 1 = Pending, 2 = In Progress, 3 = Reject, 4 = Pause
            $table->longText('description')->nullable();
            $table->date('request_date')->useCurrent();
            $table->date('completed_date')->nullable();

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
