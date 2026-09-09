<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateEmployeeLeaveRequestsTable extends Migration
{
    public function up()
    {
        if (Schema::hasTable('employee_leave_requests')) {
            return;
        }

        Schema::create('employee_leave_requests', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('tenant_id')->default(0);
            $table->integer('branch_id')->default(0);
            $table->integer('employee_id')->default(0);
            $table->string('request_type', 20);
            $table->tinyInteger('leave_type')->default(0);
            $table->date('from_date')->nullable();
            $table->date('to_date')->nullable();
            $table->time('from_time')->nullable();
            $table->time('to_time')->nullable();
            $table->string('session', 20)->nullable();
            $table->text('reason')->nullable();
            $table->string('request_status', 20)->default('pending');
            $table->integer('approved_by')->default(0);
            $table->dateTime('approved_at')->nullable();
            $table->text('approval_remark')->nullable();
            $table->integer('created_by')->default(0);
            $table->integer('updated_by')->default(0);
            $table->tinyInteger('status')->default(1);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down()
    {
        Schema::dropIfExists('employee_leave_requests');
    }
}
