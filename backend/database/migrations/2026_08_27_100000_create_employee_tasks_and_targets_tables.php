<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateEmployeeTasksAndTargetsTables extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('employee_tasks')) {
            Schema::create('employee_tasks', function (Blueprint $table) {
                $table->increments('id');
                $table->integer('tenant_id')->default(0);
                $table->integer('branch_id')->default(0);
                $table->integer('employee_id')->default(0);
                $table->string('title', 255);
                $table->text('description')->nullable();
                $table->date('task_date');
                $table->date('due_date')->nullable();
                $table->string('priority', 20)->default('medium');
                $table->string('task_status', 20)->default('pending');
                $table->integer('assigned_by')->default(0);
                $table->dateTime('completed_at')->nullable();
                $table->text('employee_remark')->nullable();
                $table->integer('created_by')->default(0);
                $table->integer('updated_by')->default(0);
                $table->tinyInteger('status')->default(1);
                $table->timestamps();
                $table->softDeletes();
                $table->index(['employee_id', 'task_date']);
            });
        }

        if (!Schema::hasTable('employee_targets')) {
            Schema::create('employee_targets', function (Blueprint $table) {
                $table->increments('id');
                $table->integer('tenant_id')->default(0);
                $table->integer('branch_id')->default(0);
                $table->integer('employee_id')->default(0);
                $table->string('period_type', 20);
                $table->date('period_start');
                $table->date('period_end');
                $table->decimal('target_amount', 15, 2)->default(0);
                $table->decimal('achieved_amount', 15, 2)->default(0);
                $table->string('target_label', 100)->nullable()->default('Collection');
                $table->text('notes')->nullable();
                $table->integer('created_by')->default(0);
                $table->integer('updated_by')->default(0);
                $table->tinyInteger('status')->default(1);
                $table->timestamps();
                $table->softDeletes();
                $table->index(['employee_id', 'period_type', 'period_start']);
            });
        }
    }

    public function down()
    {
        Schema::dropIfExists('employee_tasks');
        Schema::dropIfExists('employee_targets');
    }
}
