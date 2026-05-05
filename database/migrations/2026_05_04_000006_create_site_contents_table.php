<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('site_contents', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->text('value')->nullable();
            $table->timestamps();
        });

        DB::table('site_contents')->insert([
            ['key' => 'about_heading', 'value' => 'A refined booking experience for guests and staff.', 'created_at' => now(), 'updated_at' => now()],
            ['key' => 'about_body', 'value' => 'Villa Estella brings reservations, room discovery, and guest management together in one premium hospitality workflow.', 'created_at' => now(), 'updated_at' => now()],
            ['key' => 'services_intro', 'value' => 'From discovery to checkout, the system keeps each step clean, clear, and easy to use.', 'created_at' => now(), 'updated_at' => now()],
            ['key' => 'faqs_intro', 'value' => 'Short, useful answers that help guests move confidently from browsing to booking.', 'created_at' => now(), 'updated_at' => now()],
            ['key' => 'contact_email', 'value' => 'hello@villaestella.com', 'created_at' => now(), 'updated_at' => now()],
            ['key' => 'contact_phone', 'value' => '+63 912 345 6789', 'created_at' => now(), 'updated_at' => now()],
            ['key' => 'contact_address', 'value' => 'Sunset Boulevard, Tagaytay, Philippines', 'created_at' => now(), 'updated_at' => now()],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('site_contents');
    }
};
