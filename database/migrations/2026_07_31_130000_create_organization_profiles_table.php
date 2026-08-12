<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('organization_profiles', function (Blueprint $table): void {
            $table->id();
            $table->string('code', 20)->unique();
            $table->string('name', 180);
            $table->string('short_name', 40);
            $table->text('description')->nullable();
            $table->string('contact_email')->nullable();
            $table->string('contact_phone', 50)->nullable();
            $table->string('office_location')->nullable();
            $table->string('website')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        DB::table('organization_profiles')->insert([
            [
                'code' => 'msd',
                'name' => 'Management Services Division',
                'short_name' => 'MSD',
                'description' => 'Governance owner for university policy, circular, classification, and reporting records managed by the Management Services Division.',
                'contact_email' => 'msd.admin@iium.edu.my',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'code' => 'kcdiom',
                'name' => 'Kulliyyah, Centre, Division, Institute and Office Management',
                'short_name' => 'KCDIOM',
                'description' => 'Organization profile for policy and circular records, classifications, templates, and reporting data owned by KCDIOM.',
                'contact_email' => 'kcdiom.liaison@iium.edu.my',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('organization_profiles');
    }
};
