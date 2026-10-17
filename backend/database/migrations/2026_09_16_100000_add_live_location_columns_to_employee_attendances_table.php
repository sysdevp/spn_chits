<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddLiveLocationColumnsToEmployeeAttendancesTable extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('employee_attendances')) {
            return;
        }
        Schema::table('employee_attendances', function (Blueprint $table) {
            if (!Schema::hasColumn('employee_attendances', 'live_latitude')) {
                $table->string('live_latitude', 50)->nullable();
            }
            if (!Schema::hasColumn('employee_attendances', 'live_longitude')) {
                $table->string('live_longitude', 50)->nullable();
            }
            if (!Schema::hasColumn('employee_attendances', 'live_location_at')) {
                $table->dateTime('live_location_at')->nullable();
            }
        });
    }

    public function down()
    {
        if (!Schema::hasTable('employee_attendances')) {
            return;
        }
        Schema::table('employee_attendances', function (Blueprint $table) {
            if (Schema::hasColumn('employee_attendances', 'live_location_at')) {
                $table->dropColumn('live_location_at');
            }
            if (Schema::hasColumn('employee_attendances', 'live_longitude')) {
                $table->dropColumn('live_longitude');
            }
            if (Schema::hasColumn('employee_attendances', 'live_latitude')) {
                $table->dropColumn('live_latitude');
            }
        });
    }
}
