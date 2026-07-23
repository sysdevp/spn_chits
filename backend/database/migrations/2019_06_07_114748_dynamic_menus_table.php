<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class DynamicMenusTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('dynamic_menus', function (Blueprint $table) {
            $table->increments('id');
            $table->string('menu_title');
            $table->string('icon_class');
            $table->string('router_link');
            $table->tinyInteger('has_submenu');
            $table->integer('tab_type');
            $table->integer('parent_menu_id');
            $table->integer('created_by');
            $table->integer('updated_by');
            $table->integer('deleted_by');
            $table->softDeletes();
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
        Schema::dropIfExists('dynamic_menus');
    }
}
