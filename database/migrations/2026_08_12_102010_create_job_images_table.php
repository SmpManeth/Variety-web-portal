<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create("job_images", function (Blueprint $table) {
            $table->id();
            $table->foreignId("event_id");
            $table->string("name");
            $table->string("path");
            $table->timestamps();

            $table->unique(["event_id", "name"]);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists("job_images");
    }
};
