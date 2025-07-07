<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
   public function up(): void
    {
        Schema::create('sales', function (Blueprint $table) {
            $table->id();
            $table->decimal('total_price', 10, 2)->nullable(); // Nullable so it can be updated later
            $table->string('discount_type')->default('NONE');
            $table->timestamps();
        });
    }


    public function down(): void
    {
        Schema::dropIfExists('sales');
    }
};
