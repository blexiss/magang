<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ModifyInventoryIdNullableInAuditLogsTable extends Migration
{
    public function up()
    {
        Schema::table('audit_logs', function (Blueprint $table) {
            // Drop old foreign key first
            $table->dropForeign(['inventory_id']);

            // Change column to nullable
            $table->unsignedBigInteger('inventory_id')->nullable()->change();

            // Add foreign key with ON DELETE SET NULL
            $table->foreign('inventory_id')
                ->references('id')
                ->on('inventories')
                ->onDelete('set null');
        });
    }

    public function down()
    {
        Schema::table('audit_logs', function (Blueprint $table) {
            $table->dropForeign(['inventory_id']);

            // Change back to NOT NULL
            $table->unsignedBigInteger('inventory_id')->nullable(false)->change();

            // Add foreign key without ON DELETE SET NULL (default restrict)
            $table->foreign('inventory_id')
                ->references('id')
                ->on('inventories');
        });
    }
}

