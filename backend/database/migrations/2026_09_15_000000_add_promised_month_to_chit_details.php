<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddPromisedMonthToChitDetails extends Migration
{
    public function up()
    {
        if (!Schema::hasColumn('chit_details', 'promised_month')) {
            Schema::table('chit_details', function (Blueprint $table) {
                $table->date('promised_month')->nullable()->after('due_date');
            });
        }
    }

    public function down()
    {
        if (Schema::hasColumn('chit_details', 'promised_month')) {
            Schema::table('chit_details', function (Blueprint $table) {
                $table->dropColumn('promised_month');
            });
        }
    }
}