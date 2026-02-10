<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
  public function up(): void
  {
    Schema::table('contact_messages', function (Blueprint $table) {
      $table->timestamp('contacted_at')->nullable()->after('read_at');
      $table->index(['read_at', 'contacted_at']);
      $table->index('created_at');
    });
  }

  public function down(): void
  {
    Schema::table('contact_messages', function (Blueprint $table) {
      $table->dropIndex(['contact_messages_read_at_contacted_at_index']);
      $table->dropIndex(['contact_messages_created_at_index']);
      $table->dropColumn('contacted_at');
    });
  }
};
