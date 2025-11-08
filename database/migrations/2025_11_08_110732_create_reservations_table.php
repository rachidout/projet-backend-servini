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
        Schema::create('reservations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_prestataire')->constrained('prestataires')->onDelete('cascade');
            $table->foreignId('id_service')->constrained('services')->onDelete('cascade');
            $table->string('client_nom');
            $table->string('client_prenom');
            $table->string('client_email');
            $table->string('client_telephone');
            $table->text('client_adresse');
            $table->text('description_du_besoin');
            $table->date('date');
            $table->time('heure');
            $table->enum('statut', [
                'en_attente',
                'confirmee',
                'annulee'
            ])->default('en_attente');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reservations');
    }
};
