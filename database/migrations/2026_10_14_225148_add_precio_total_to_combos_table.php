<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
public function up()
{
    Schema::table('combos', function (Blueprint $table) {
        $table->decimal('precio_total', 10, 2)->after('nombre')->default(0);
    });
}

public function down()
{
    Schema::table('combos', function (Blueprint $table) {
        $table->dropColumn('precio_total');
    });
}
};
