<?php

namespace Database\Seeders;

use App\Models\PolicyDocument;
use App\Models\TopicCategory;
use App\Models\LookupValue;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call(OfficialTopicHierarchySeeder::class);

        foreach ([
            ['type' => 'DOCUMENT_TYPE', 'code' => 'policy', 'description' => 'Policy Document', 'sort_order' => 1],
            ['type' => 'DOCUMENT_TYPE', 'code' => 'guideline', 'description' => 'Guideline Document', 'sort_order' => 2],
            ['type' => 'DOCUMENT_TYPE', 'code' => 'circular', 'description' => 'Circular Document', 'sort_order' => 3],
            ['type' => 'DOCUMENT_STATUS', 'code' => 'draft', 'description' => 'Draft', 'sort_order' => 1],
            ['type' => 'DOCUMENT_STATUS', 'code' => 'published', 'description' => 'Active', 'sort_order' => 2],
            ['type' => 'DOCUMENT_STATUS', 'code' => 'superseded', 'description' => 'Superceded', 'sort_order' => 3],
            ['type' => 'DOCUMENT_STATUS', 'code' => 'inactive', 'description' => 'Inactive', 'sort_order' => 90, 'is_active' => false],
            ['type' => 'DOCUMENT_STATUS', 'code' => 'archived', 'description' => 'Archived', 'sort_order' => 91, 'is_active' => false],
        ] as $lookup) {
            LookupValue::updateOrCreate(['type' => $lookup['type'], 'code' => $lookup['code']], $lookup + ['is_active' => true]);
        }

        foreach ([
            ['name' => 'Leave', 'slug' => 'leave'],
            ['name' => 'Recruitment', 'slug' => 'recruitment'],
            ['name' => 'Training', 'slug' => 'training'],
            ['name' => 'Performance', 'slug' => 'performance'],
            ['name' => 'Welfare', 'slug' => 'welfare'],
        ] as $category) {
            TopicCategory::query()->updateOrCreate(
                ['slug' => $category['slug']],
                ['name' => $category['name'], 'is_active' => true]
            );
        }

        $msdAdmin = User::query()->updateOrCreate(
            ['email' => 'msd.admin@iium.edu.my'],
            [
                'name' => 'MSD Administrator',
                'staff_id' => 'MSD0001',
                'cas_username' => 'msd.admin',
                'role' => 'system_admin',
                'unit' => 'all',
                'is_active' => true,
                'password' => 'Password123!',
            ]
        );

        $kcdiomLiaison = User::query()->updateOrCreate(
            ['email' => 'kcdiom.liaison@iium.edu.my'],
            [
                'name' => 'KCDIOM Liaison',
                'staff_id' => 'KCD0001',
                'cas_username' => 'kcdiom.liaison',
                'role' => 'policy_manager',
                'unit' => 'kcdiom',
                'is_active' => true,
                'password' => 'Password123!',
            ]
        );

        $staffUser = User::query()->updateOrCreate(
            ['email' => 'staff.user@iium.edu.my'],
            [
                'name' => 'Staff User',
                'staff_id' => 'STF0001',
                'cas_username' => 'staff.user',
                'role' => 'staff_user',
                'unit' => 'msd',
                'is_active' => true,
                'password' => 'Password123!',
            ]
        );

        PolicyDocument::query()->updateOrCreate(
            ['title' => 'University Public Circular', 'version_number' => 1],
            [
                'document_type' => 'circular',
                'topic_category' => null,
                'content' => 'Published circular visible to all staff.',
                'access_scope' => 'all',
                'owner_unit' => 'msd',
                'status' => 'published',
                'is_circular' => true,
                'parent_document_id' => null,
                'created_by' => $msdAdmin->id,
                'published_at' => now(),
            ]
        );

        PolicyDocument::query()->updateOrCreate(
            ['title' => 'MSD Internal Leave Policy', 'version_number' => 1],
            [
                'document_type' => 'policy',
                'topic_category' => 'leave',
                'content' => 'Internal policy visible to MSD viewers.',
                'access_scope' => 'msd',
                'owner_unit' => 'msd',
                'status' => 'published',
                'is_circular' => false,
                'parent_document_id' => null,
                'created_by' => $msdAdmin->id,
                'published_at' => now(),
            ]
        );

        PolicyDocument::query()->updateOrCreate(
            ['title' => 'KCDIOM Draft Circular', 'version_number' => 1],
            [
                'document_type' => 'circular',
                'topic_category' => null,
                'content' => 'Draft circular restricted to KCDIOM liaison management view.',
                'access_scope' => 'kcdiom',
                'owner_unit' => 'kcdiom',
                'status' => 'draft',
                'is_circular' => true,
                'parent_document_id' => null,
                'created_by' => $kcdiomLiaison->id,
                'published_at' => null,
            ]
        );

        PolicyDocument::query()->updateOrCreate(
            ['title' => 'Staff Onboarding Guideline', 'version_number' => 1],
            [
                'document_type' => 'guideline',
                'topic_category' => 'training',
                'content' => 'Published guideline intended for general staff usage.',
                'access_scope' => 'all',
                'owner_unit' => 'msd',
                'status' => 'published',
                'is_circular' => false,
                'parent_document_id' => null,
                'created_by' => $staffUser->id,
                'published_at' => now(),
            ]
        );
    }
}
