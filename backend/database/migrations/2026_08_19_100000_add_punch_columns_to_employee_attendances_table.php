<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddPunchColumnsToEmployeeAttendancesTable extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('employee_attendances')) {
            return;
        }

        $columns = [
            'check_in_time' => function (Blueprint $table) {
                $table->time('check_in_time')->nullable();
            },
            'check_out_time' => function (Blueprint $table) {
                $table->time('check_out_time')->nullable();
            },
            'check_in_latitude' => function (Blueprint $table) {
                $table->string('check_in_latitude', 50)->nullable();
            },
            'check_in_longitude' => function (Blueprint $table) {
                $table->string('check_in_longitude', 50)->nullable();
            },
            'check_out_latitude' => function (Blueprint $table) {
                $table->string('check_out_latitude', 50)->nullable();
            },
            'check_out_longitude' => function (Blueprint $table) {
                $table->string('check_out_longitude', 50)->nullable();
            },
            'punch_status' => function (Blueprint $table) {
                $table->string('punch_status', 20)->nullable()->default('none');
            },
        ];

        foreach ($columns as $col => $callback) {
            if (!Schema::hasColumn('employee_attendances', $col)) {
                Schema::table('employee_attendances', $callback);
            }
        }
    }

    public function down()
    {
        if (!Schema::hasTable('employee_attendances')) {
            return;
        }

        Schema::table('employee_attendances', function (Blueprint $table) {
            $cols = [];
            foreach ([
                'check_in_time', 'check_out_time',
                'check_in_latitude', 'check_in_longitude',
                'check_out_latitude', 'check_out_longitude',
                'punch_status',
            ] as $col) {
                if (Schema::hasColumn('employee_attendances', $col)) {
                    $cols[] = $col;
                }
            }
            if (!empty($cols)) {
                $table->dropColumn($cols);
            }
        });
    }
}
