<?php

namespace Tests\Feature;

use App\Models\TopicCategory;
use App\Models\TopicSubtopic;
use App\Models\TopicDetail;
use Database\Seeders\OfficialTopicHierarchySeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OfficialTopicHierarchySeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_loads_the_complete_official_hierarchy_without_duplicates(): void
    {
        $this->seed(OfficialTopicHierarchySeeder::class);
        $this->seed(OfficialTopicHierarchySeeder::class);

        $expected = [
            'service-matters' => 8,
            'service-scheme-employment' => 3,
            'human-resource-development' => 2,
            'compensation-benefits' => 9,
            'organisational-administration' => 4,
            'retirement-benefits' => 2,
        ];

        foreach ($expected as $slug => $topicCount) {
            $category = TopicCategory::query()->where('slug', $slug)->firstOrFail();

            $this->assertTrue($category->is_active);
            $this->assertSame($topicCount, $category->subtopics()->count());
        }

        $this->assertSame(6, TopicCategory::query()->whereIn('slug', array_keys($expected))->count());
        $this->assertSame(28, TopicSubtopic::query()->count());
        $this->assertDatabaseHas('topic_subtopics', ['name' => 'SM.1 Appointment']);
        $this->assertDatabaseHas('topic_subtopics', ['name' => 'RB.2 Gratuity for Contract Staff']);
        $appointment = TopicSubtopic::query()->where('name', 'SM.1 Appointment')->firstOrFail();
        $this->assertSame(3, TopicDetail::query()->where('main_topic_id', $appointment->id)->count());
        $this->assertDatabaseHas('topic_details', ['main_topic_id' => $appointment->id, 'name' => 'Salary Determination']);
    }
}
