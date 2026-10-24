<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $t) {
            $t->string('username')->nullable()->unique()->after('name');
            $t->string('role')->default('visitor')->after('username');
            $t->text('bio')->nullable()->after('role');
            $t->string('avatar_url')->nullable()->after('bio');
            $t->string('avatar_path')->nullable()->after('avatar_url');
            $t->string('google_id')->nullable()->after('avatar_path');
            $t->tinyInteger('budget_level')->default(2)->after('google_id');
            $t->integer('review_points')->default(0)->after('budget_level');
            $t->string('rank')->default('Newcomer')->after('review_points');
            $t->string('website')->nullable()->after('rank');
            $t->string('social_instagram')->nullable()->after('website');
            $t->string('social_facebook')->nullable()->after('social_instagram');
            $t->string('social_x')->nullable()->after('social_facebook');
        });

        Schema::create('interests', function (Blueprint $t) {
            $t->id(); $t->string('name')->unique(); $t->string('icon')->default('fa-tag'); $t->timestamps();
        });

        Schema::create('destinations', function (Blueprint $t) {
            $t->id();
            $t->foreignId('owner_id')->constrained('users')->cascadeOnDelete();
            $t->string('name'); $t->text('description'); $t->string('image_url')->nullable();
            $t->string('city')->index(); $t->string('country')->default('South Africa');
            $t->decimal('latitude', 10, 7)->nullable(); $t->decimal('longitude', 10, 7)->nullable();
            $t->decimal('estimated_cost', 10, 2); $t->integer('capacity')->nullable();
            $t->decimal('average_rating', 3, 2)->default(0);
            $t->integer('review_count')->default(0);
            $t->integer('booking_count')->default(0);
            $t->integer('view_count')->default(0);
            $t->timestamps();
        });

        Schema::create('events', function (Blueprint $t) {
            $t->id();
            $t->foreignId('creator_id')->constrained('users')->cascadeOnDelete();
            $t->foreignId('destination_id')->nullable()->constrained()->nullOnDelete();
            $t->string('title'); $t->text('description'); $t->string('image_url')->nullable();
            $t->string('venue'); $t->string('city')->index();
            $t->decimal('latitude', 10, 7)->nullable(); $t->decimal('longitude', 10, 7)->nullable();
            $t->dateTime('start_date')->index(); $t->dateTime('end_date');
            $t->dateTime('booking_end_date')->nullable();
            $t->string('external_booking_url')->nullable();
            $t->integer('capacity')->nullable();
            $t->decimal('price', 10, 2)->default(0);
            $t->decimal('average_rating', 3, 2)->default(0);
            $t->integer('review_count')->default(0);
            $t->integer('booking_count')->default(0);
            $t->timestamps();
        });

        Schema::create('reviews', function (Blueprint $t) {
            $t->id();
            $t->foreignId('author_id')->constrained('users')->cascadeOnDelete();
            $t->foreignId('destination_id')->nullable()->constrained()->cascadeOnDelete();
            $t->foreignId('event_id')->nullable()->constrained()->cascadeOnDelete();
            $t->tinyInteger('rating'); $t->text('body'); $t->timestamps();
        });

        Schema::create('bookings', function (Blueprint $t) {
            $t->id();
            $t->foreignId('user_id')->constrained()->cascadeOnDelete();
            $t->foreignId('destination_id')->nullable()->constrained()->cascadeOnDelete();
            $t->foreignId('event_id')->nullable()->constrained()->cascadeOnDelete();
            $t->string('status')->default('confirmed');
            $t->integer('guests')->default(1);
            $t->text('notes')->nullable();
            $t->dateTime('booked_date')->nullable();
            $t->timestamps();
        });

        Schema::create('posts', function (Blueprint $t) {
            $t->id();
            $t->foreignId('author_id')->constrained('users')->cascadeOnDelete();
            $t->string('type')->default('visit');           // visit|business|event
            $t->foreignId('destination_id')->nullable()->constrained()->nullOnDelete();
            $t->foreignId('event_id')->nullable()->constrained()->nullOnDelete();
            $t->decimal('location_rating', 3, 2)->nullable();
            $t->text('content'); $t->string('image_url')->nullable();
            $t->integer('like_count')->default(0); $t->integer('comment_count')->default(0);
            $t->timestamps();
        });

        Schema::create('post_media', function (Blueprint $t) {
            $t->id();
            $t->foreignId('post_id')->constrained()->cascadeOnDelete();
            $t->string('kind')->default('image');           // image|video
            $t->string('path');
            $t->unsignedTinyInteger('position')->default(0);
            $t->timestamps();
        });

        Schema::create('comments', function (Blueprint $t) {
            $t->id();
            $t->foreignId('post_id')->constrained()->cascadeOnDelete();
            $t->foreignId('author_id')->constrained('users')->cascadeOnDelete();
            $t->foreignId('parent_id')->nullable()->constrained('comments')->cascadeOnDelete();
            $t->text('body'); $t->timestamps();
        });

        Schema::create('comment_reactions', function (Blueprint $t) {
            $t->id();
            $t->foreignId('comment_id')->constrained()->cascadeOnDelete();
            $t->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $t->string('type');                              // like|dislike
            $t->unique(['comment_id', 'user_id']);
            $t->timestamps();
        });

        Schema::create('conversations', function (Blueprint $t) {
            $t->id(); $t->timestamps();
        });

        Schema::create('conversation_user', function (Blueprint $t) {
            $t->foreignId('conversation_id')->constrained()->cascadeOnDelete();
            $t->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $t->primary(['conversation_id', 'user_id']);
            $t->timestamps();
        });

        Schema::create('messages', function (Blueprint $t) {
            $t->id();
            $t->foreignId('conversation_id')->constrained()->cascadeOnDelete();
            $t->foreignId('sender_id')->constrained('users')->cascadeOnDelete();
            $t->text('body')->nullable();
            $t->string('share_type')->nullable();            // post|destination|event
            $t->unsignedBigInteger('share_id')->nullable();
            $t->timestamps();
        });

        Schema::create('notifications', function (Blueprint $t) {
            $t->id();
            $t->foreignId('user_id')->constrained()->cascadeOnDelete();
            $t->string('message'); $t->string('link')->nullable();
            $t->boolean('is_read')->default(false);
            $t->timestamps();
        });

        Schema::create('user_interests', function (Blueprint $t) {
            $t->foreignId('user_id')->constrained()->cascadeOnDelete();
            $t->foreignId('interest_id')->constrained()->cascadeOnDelete();
            $t->primary(['user_id', 'interest_id']);
        });
        Schema::create('destination_interests', function (Blueprint $t) {
            $t->foreignId('destination_id')->constrained()->cascadeOnDelete();
            $t->foreignId('interest_id')->constrained()->cascadeOnDelete();
            $t->primary(['destination_id', 'interest_id']);
        });
        Schema::create('event_interests', function (Blueprint $t) {
            $t->foreignId('event_id')->constrained()->cascadeOnDelete();
            $t->foreignId('interest_id')->constrained()->cascadeOnDelete();
            $t->primary(['event_id', 'interest_id']);
        });
    }

    public function down(): void
    {
        foreach (['event_interests','destination_interests','user_interests','notifications',
            'messages','conversation_user','conversations','comment_reactions','comments',
            'post_media','posts','bookings','reviews','events','destinations','interests'] as $table) {
            Schema::dropIfExists($table);
        }
        Schema::table('users', function (Blueprint $t) {
            $t->dropColumn(['username','role','bio','avatar_url','avatar_path','google_id',
                'budget_level','review_points','rank','website','social_instagram','social_facebook','social_x']);
        });
    }
};