<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Creates the table that records barcodes that have been shared.
     *
     * @return void
     */
    public function up()
    {
        Schema::create("shared_barcodes", function (Blueprint $table) {
            $table->id();
            $table->bigInteger("shared_by")->unsigned()->nullable();
            $table->bigInteger("original_barcode_id")->unsigned()->nullable();
            $table->string("name", 200)->default("");
            $table->text("data")->default("");
            $table->string("generator", 20)->default("");
            $table->dateTime("expires_at")->default("9999-12-31 23:59:59");
            $table->timestamps();
            $table->softDeletes();
            $table->foreign("shared_by")->references("id")->on("users")->nullOnDelete();
            $table->foreign("original_barcode_id")->references("id")->on("barcodes")->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists("shared_barcodes");
    }
};
