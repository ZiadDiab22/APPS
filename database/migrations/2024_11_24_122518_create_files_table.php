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
        Schema::create('files', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->string('type');
            $table->longText('content');
            $table->boolean('available')->nullable()->default(1);
            $table->unsignedInteger('creater_id');
            $table->unsignedInteger('reserver_id')->nullable()->default(null);
            $table->timestamps();
            $table->foreign('creater_id')->references('id')
                ->on('users')->onDelete('cascade');
            $table->foreign('reserver_id')->references('id')
                ->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('files');
    }
};
