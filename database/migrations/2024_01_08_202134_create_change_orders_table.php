<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('change_orders', function (Blueprint $table) {
            $table->id();
            $table->string('subject');
            $table->string('from');
            $table->string('project');
            $table->string('sponsor');
            $table->string('implementer');
            $table->text('system_changes');
            $table->text('description');
            $table->text('effect');
            $table->text('reason');
            $table->dateTime('change_date');
            $table->text('downtime');
            $table->text('back_out_plan');
            $table->string('communication');
            $table->text('comments');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('change_orders');
    }
};
