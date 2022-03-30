<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Creates the many-to-many link table for barcodes <-> tags.
     *
     * @return void
     */
    public function up()
    {
        Schema::create("barcode_tags", function (Blueprint $table) {
            $table->id();
            $table->bigInteger("barcode_id")->unsigned();
            $table->bigInteger("tag_id")->unsigned();
            $table->softDeletes();
            $table->timestamps();
            $table->foreign("barcode_id")->references("id")->on("barcodes")->cascadeOnDelete();
            $table->foreign("tag_id")->references("id")->on("tags")->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists("barcode_tags");
    }
};
