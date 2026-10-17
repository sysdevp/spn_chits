<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCustomerAppLeadsTable extends Migration
{
    public function up()
    {
        if (Schema::hasTable('customer_app_leads')) {
            return;
        }
        Schema::create('customer_app_leads', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('tenant_id')->default(0);
            $table->integer('branch_id')->default(0);
            $table->string('lead_type', 20)->default('referral');
            $table->integer('referrer_customer_id')->default(0);
            $table->string('name', 150);
            $table->string('mobile_no', 20);
            $table->string('location', 255)->nullable();
            $table->text('remarks')->nullable();
            $table->tinyInteger('status')->default(0);
            $table->text('status_remarks')->nullable();
            $table->integer('updated_by')->default(0);
            $table->timestamps();
            $table->index(['tenant_id', 'lead_type']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('customer_app_leads');
    }
}
