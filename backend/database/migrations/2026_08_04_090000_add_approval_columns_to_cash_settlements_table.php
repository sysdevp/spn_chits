<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddApprovalColumnsToCashSettlementsTable extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('cash_settlements')) {
            return;
        }

        if (!Schema::hasColumn('cash_settlements', 'is_approved')) {
            Schema::table('cash_settlements', function (Blueprint $table) {
                $table->tinyInteger('is_approved')->default(0);
            });
        }
        if (!Schema::hasColumn('cash_settlements', 'approval_remarks')) {
            Schema::table('cash_settlements', function (Blueprint $table) {
                $table->text('approval_remarks')->nullable();
            });
        }
        if (!Schema::hasColumn('cash_settlements', 'approved_by')) {
            Schema::table('cash_settlements', function (Blueprint $table) {
                $table->integer('approved_by')->nullable()->default(0);
            });
        }
        if (!Schema::hasColumn('cash_settlements', 'approved_at')) {
            Schema::table('cash_settlements', function (Blueprint $table) {
                $table->timestamp('approved_at')->nullable();
            });
        }
    }

    public function down()
    {
        if (!Schema::hasTable('cash_settlements')) {
            return;
        }
        Schema::table('cash_settlements', function (Blueprint $table) {
            $cols = [];
            foreach (['is_approved', 'approval_remarks', 'approved_by', 'approved_at'] as $col) {
                if (Schema::hasColumn('cash_settlements', $col)) {
                    $cols[] = $col;
                }
            }
            if (!empty($cols)) {
                $table->dropColumn($cols);
            }
        });
    }
}
