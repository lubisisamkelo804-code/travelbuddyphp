<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('conversation_user', function (Blueprint $t) {
            $t->timestamp('last_read_at')->nullable()->after('user_id');
        });
    }

    public function down(): void
    {
        Schema::table('conversation_user', function (Blueprint $t) {
            $t->dropColumn('last_read_at');
        });
    }
};
