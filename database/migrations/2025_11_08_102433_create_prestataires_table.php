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
        Schema::create('prestataires', function (Blueprint $table) {
            $table->id();
            $table->string('nom');
            $table->string('prenom');
            $table->string('email')->unique();
            $table->string('telephone')->unique();
            $table->string('image')->nullable();
            $table->decimal('prix_heure',5,2)->default(0);
            $table->text('bio')->nullable();
            $table->string('ville');
            $table->string('zone');
            $table->string('password');
            $table->string('carte_identite')->nullable();
            $table->decimal('note_moyenne',3,1)->default(0); // la valuer momkin tkon XX.Y
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('prestataires');
    }
};
