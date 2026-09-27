<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use TelegramBotEssentials\Essence\Models\Bot;
use TelegramBotEssentials\Essence\Models\BotUser;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('admin_payment_attempts', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(Bot::class)->constrained();
            // The admin who paid, not the member the invoice belongs to.
            $table->foreignIdFor(BotUser::class)->constrained();
            $table->decimal('amount', 65, 30);
            $table->string('status')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('admin_payment_attempts');
    }
};
