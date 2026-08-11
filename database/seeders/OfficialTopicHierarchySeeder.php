<?php

namespace Database\Seeders;

use App\Models\TopicCategory;
use App\Models\TopicSubtopic;
use App\Models\TopicDetail;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class OfficialTopicHierarchySeeder extends Seeder
{
    public function run(): void
    {
        $hierarchy = [
            'SM' => ['Service Matters', [
                'SM.1' => 'Appointment',
                'SM.2' => 'Placement & Staffing',
                'SM.3' => 'Promotion',
                'SM.4' => 'Performance Management',
                'SM.5' => 'Recognition',
                'SM.6' => 'Career Advancement',
                'SM.7' => 'Code of Conduct & Disciplinary Management',
                'SM.8' => 'Examination',
            ]],
            'SS' => ['Service Scheme & Employment', [
                'SS.1' => 'Service Scheme & Employment',
                'SS.2' => 'Professional Qualification',
                'SS.3' => 'Remuneration System',
            ]],
            'HRD' => ['Human Resource Development', [
                'HRD.1' => 'Human Resource Development Policy',
                'HRD.2' => 'Training & Sponsorship Policy',
            ]],
            'CB' => ['Compensation & Benefits', [
                'CB.1' => 'Clothing Facilities',
                'CB.2' => 'Medical Facilities',
                'CB.3' => 'Government Housing Facilities',
                'CB.4' => 'Working Hours',
                'CB.5' => 'Leave Facilities',
                'CB.6' => 'Inter-Regional Fare Facility',
                'CB.7' => 'Other Facilities',
                'CB.8' => 'Allowances',
                'CB.9' => 'Staff Khairat Fund',
            ]],
            'OA' => ['Organisational Administration', [
                'OA.1' => 'Office Administration & Management',
                'OA.2' => 'Human Resource Information Management',
                'OA.3' => 'Psychological Management',
                'OA.4' => 'Employee Employer Relations',
            ]],
            'RB' => ['Retirement Benefits', [
                'RB.1' => 'Retirement & Retirement Benefits',
                'RB.2' => 'Gratuity for Contract Staff',
            ]],
        ];

        foreach ($hierarchy as $categoryCode => [$categoryName, $topics]) {
            $category = TopicCategory::query()->updateOrCreate(
                ['slug' => Str::slug($categoryName)],
                ['name' => $categoryCode.' — '.$categoryName, 'is_active' => true]
            );

            foreach ($topics as $topicCode => $topicName) {
                $displayName = $topicCode.' '.$topicName;

                TopicSubtopic::query()->updateOrCreate(
                    ['slug' => Str::slug($displayName)],
                    [
                        'topic_category_id' => $category->id,
                        'name' => $displayName,
                        'is_active' => true,
                    ]
                );
            }
        }

        $appointment = TopicSubtopic::query()->where('slug', 'sm1-appointment')->first();
        if ($appointment) {
            foreach (['Salary Determination', 'Statutory Deduction', 'Terms and Conditions'] as $name) {
                TopicDetail::query()->updateOrCreate(
                    ['slug' => Str::slug('SM.1 '.$name)],
                    ['main_topic_id' => $appointment->id, 'name' => $name, 'is_active' => true]
                );
            }
        }
    }
}
