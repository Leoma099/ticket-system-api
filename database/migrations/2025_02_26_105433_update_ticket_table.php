<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateTicketTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('tickets', function (Blueprint $table) {
            // Add 'account_id' column for existing and non-existing accounts
            if (!Schema::hasColumn('tickets', 'account_id')) {
                $table->bigInteger('account_id')->nullable();  // Nullable for walk-in users
            }

            // Add 'assigned_by' column for staff/admin who assigns the ticket
            if (!Schema::hasColumn('tickets', 'assigned_by')) {
                $table->tinyInteger('assigned_by')->nullable();  // Nullable for now
            }

            // Optionally modify the 'status' column (e.g., set default to 1 - Pending)
            $table->tinyInteger('status')->default(1)->change(); // Default to "Pending" (1)

            // Optionally add new columns if required (for example, the 'photo' column)
            // $table->string('photo')->nullable(); // Uncomment if you need to store photos
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('tickets', function (Blueprint $table) {
            // Drop the newly added columns during rollback
            $table->dropColumn('account_id');
            $table->dropColumn('assigned_by');
        });
    }
}
