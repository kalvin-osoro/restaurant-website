<?php
namespace App\Infrastructure\Persistence\Eloquent\Repositories;
use App\Domain\WebsiteContent\Contracts\ContentWriter;
use App\Infrastructure\Persistence\Eloquent\Models\{Attorney, CaseCategory, CaseStudy, PracticeArea};
use Illuminate\Support\Facades\DB;
final class EloquentContentWriter implements ContentWriter
{
    public function save(array $data): void
    {
            DB::transaction(function () use ($data) {
                foreach ($data['practiceAreas'] ?? [] as $area) {
                    PracticeArea::updateOrCreate(['slug' => $area['slug']], ['name' => $area['name'], 'display_order' => $area['displayOrder'] ?? 0]);
                }
                foreach ($data['caseCategories'] ?? [] as $category) {
                    CaseCategory::updateOrCreate(['slug' => $category['slug']], ['name' => $category['name'], 'display_order' => $category['displayOrder'] ?? 0]);
                }
                foreach ($data['attorneys'] ?? [] as $record) {
                    $areas = PracticeArea::whereIn('slug', array_unique($record['practiceAreas']))->pluck('id');
                    if ($areas->count() !== count(array_unique($record['practiceAreas']))) {
                        throw new \InvalidArgumentException('An attorney references an unknown practice-area slug.');
                    }
                    $attorney = Attorney::updateOrCreate(['slug' => $record['slug']], [
                        'name' => $record['name'], 'role' => $record['role'], 'bio' => $record['bio'],
                        'image_url' => $record['imageUrl'], 'email' => $record['email'], 'phone' => $record['phone'],
                        'education' => $record['education'], 'admissions' => $record['admissions'], 'notable_cases' => $record['notableCases'],
                        'status' => $record['status'] ?? 'draft', 'display_order' => $record['displayOrder'] ?? 0,
                    ]);
                    $attorney->practiceAreas()->sync($areas->all());
                }
                foreach ($data['cases'] ?? [] as $record) {
                    $category = CaseCategory::where('slug', $record['category'])->first();
                    if (!$category) {
                        throw new \InvalidArgumentException('A case references an unknown category slug.');
                    }
                    CaseStudy::updateOrCreate(['slug' => $record['slug']], [
                        'case_category_id' => $category->id, 'title' => $record['title'], 'description' => $record['description'],
                        'value' => $record['value'] ?? null, 'timeline' => $record['timeline'] ?? null,
                        'jurisdiction' => $record['jurisdiction'] ?? null, 'outcome_summary' => $record['outcomeSummary'],
                        'key_details' => $record['keyDetails'], 'image_url' => $record['imageUrl'] ?? null,
                        'client_industry' => $record['clientIndustry'], 'confidential' => $record['confidential'] ?? true,
                        'status' => $record['status'] ?? 'draft', 'display_order' => $record['displayOrder'] ?? 0,
                        'featured_position' => $record['featuredPosition'] ?? null,
                    ]);
                }
            });
    }
}
