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
        Schema::table('campaign_sends', function (Blueprint $table) {
            $table->unique(['campaign_id', 'contact_id'], 'campaign_sends_campaign_contact_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('campaign_sends', function (Blueprint $table) {
            $table->dropUnique('campaign_sends_campaign_contact_unique');
        });
    }
};
