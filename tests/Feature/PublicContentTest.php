<?php

namespace Tests\Feature;

use App\Infrastructure\Persistence\Eloquent\Models\{Attorney, CaseCategory, CaseStudy, PracticeArea};
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PublicContentTest extends TestCase
{
    use RefreshDatabase;

    private function attorney(string $slug, array $attributes = []): Attorney
    {
        return Attorney::create(array_merge([
            'slug' => $slug, 'name' => 'Example '.$slug, 'role' => 'Partner', 'bio' => 'Approved public biography.',
            'image_url' => 'https://example.com/profile.jpg', 'email' => 'profile@example.com', 'phone' => '+254712345678',
            'education' => ['Example qualification'], 'admissions' => ['Example admission'],
            'notable_cases' => ['Approved summary'], 'status' => 'published',
        ], $attributes));
    }

    private function study(string $slug, array $attributes = []): CaseStudy
    {
        $category = CaseCategory::firstOrCreate(['slug' => 'corporate-governance'], ['name' => 'Corporate Governance']);
        return CaseStudy::create(array_merge([
            'slug' => $slug, 'case_category_id' => $category->id, 'title' => 'Example '.$slug,
            'description' => 'Approved summary.', 'outcome_summary' => 'Approved outcome.',
            'key_details' => ['Approved detail'], 'client_industry' => 'Technology',
            'status' => 'published', 'confidential' => false,
        ], $attributes));
    }

    public function test_attorney_profiles_match_frontend_contract_and_update_from_database(): void
    {
        $profile = $this->attorney('public-partner');
        $area = PracticeArea::create(['slug' => 'corporate-law', 'name' => 'Corporate Law']);
        $profile->practiceAreas()->attach($area);
        $this->getJson('/api/attorneys/public-partner')->assertOk()->assertExactJson(['data' => [
            'id' => 'public-partner', 'name' => 'Example public-partner', 'role' => 'Partner',
            'bio' => 'Approved public biography.', 'practiceAreas' => ['Corporate Law'],
            'imageUrl' => 'https://example.com/profile.jpg', 'email' => 'profile@example.com', 'phone' => '+254712345678',
            'education' => ['Example qualification'], 'admissions' => ['Example admission'], 'notableCases' => ['Approved summary'],
        ]]);
        $profile->update(['bio' => 'Updated approved biography.']);
        $this->getJson('/api/attorneys')->assertJsonPath('data.0.bio', 'Updated approved biography.');
        $this->assertDatabaseHas('tbl_request_logs', ['method' => 'GET', 'status' => 200]);
    }

    public function test_unpublished_profiles_are_absent_from_lists_search_detail_and_taxonomy(): void
    {
        $this->attorney('visible');
        foreach (['draft', 'archived'] as $status) {
            $profile = $this->attorney($status, ['status' => $status, 'name' => 'Secret name']);
            $area = PracticeArea::create(['slug' => $status.'-area', 'name' => 'Secret '.$status]);
            $profile->practiceAreas()->attach($area);
            $this->getJson('/api/attorneys/'.$status)->assertNotFound();
        }
        $this->getJson('/api/attorneys')->assertJsonCount(1, 'data')->assertJsonPath('data.0.id', 'visible');
        $this->getJson('/api/attorneys?q=Secret')->assertJsonCount(0, 'data');
        $this->getJson('/api/practice-areas')->assertExactJson(['data' => []]);
        $this->getJson('/api/attorneys/missing')->assertNotFound();
    }

    public function test_attorney_search_is_literal_filters_work_and_pagination_is_stable(): void
    {
        $a = $this->attorney('a', ['name' => '100% expert_under_score']);
        $this->attorney('b', ['name' => '1000 expertXunderXscore']);
        $area = PracticeArea::create(['slug' => 'litigation', 'name' => 'Litigation']);
        $a->practiceAreas()->attach($area);
        foreach (['100%', '_under_', '!'] as $term) {
            $response = $this->getJson('/api/attorneys?'.http_build_query(['q' => $term]))->assertOk();
            $response->assertJsonCount($term === '!' ? 0 : 1, 'data');
        }
        foreach (['litigation', 'Litigation'] as $filter) {
            $this->getJson('/api/attorneys?'.http_build_query(['practiceArea' => $filter]))->assertJsonPath('data.0.id', 'a')->assertJsonCount(1, 'data');
        }
        $first = $this->getJson('/api/attorneys?perPage=1')->assertJsonPath('data.0.id', 'a')->assertJsonPath('meta.total', 2);
        $this->getJson($first->json('links.next'))->assertJsonPath('data.0.id', 'b')->assertJsonPath('links.next', null);
        $this->getJson('/api/practice-areas')->assertExactJson(['data' => [['id' => 'litigation', 'name' => 'Litigation']]]);
    }

    public function test_confidential_and_unpublished_cases_are_excluded_everywhere(): void
    {
        $this->study('approved');
        $privateCategory = CaseCategory::create(['slug' => 'private-category', 'name' => 'Intellectual Property']);
        foreach (['draft', 'archived', 'confidential'] as $slug) {
            $this->study($slug, [
                'status' => $slug === 'confidential' ? 'published' : $slug,
                'confidential' => $slug === 'confidential', 'case_category_id' => $privateCategory->id,
                'featured_position' => 1, 'description' => 'Secret summary.',
            ]);
            $this->getJson('/api/cases/'.$slug)->assertNotFound();
        }
        $this->getJson('/api/cases')->assertJsonCount(1, 'data')->assertJsonPath('data.0.id', 'approved')->assertDontSee('Secret');
        $this->getJson('/api/cases?featured=1')->assertJsonCount(0, 'data');
        $this->getJson('/api/cases?category=private-category')->assertJsonCount(0, 'data');
        $this->getJson('/api/case-categories')->assertExactJson(['data' => [['id' => 'corporate-governance', 'name' => 'Corporate Governance']]]);
        $this->getJson('/api/cases/missing')->assertNotFound();
    }

    public function test_case_contract_optional_fields_featured_order_and_category_filter(): void
    {
        $this->study('ordinary', ['display_order' => 0]);
        $this->study('featured-second', ['featured_position' => 2]);
        $this->study('featured-first', ['featured_position' => 1, 'value' => '$100', 'image_url' => 'https://example.com/case.jpg']);
        $this->getJson('/api/cases?featured=1')->assertJsonCount(2, 'data')
            ->assertJsonPath('data.0.id', 'featured-first')->assertJsonPath('data.1.id', 'featured-second');
        foreach (['corporate-governance', 'Corporate Governance'] as $filter) {
            $this->getJson('/api/cases?'.http_build_query(['category' => $filter]))->assertJsonCount(3, 'data');
        }
        $this->getJson('/api/cases/ordinary')->assertExactJson(['data' => [
            'id' => 'ordinary', 'title' => 'Example ordinary', 'category' => 'Corporate Governance',
            'description' => 'Approved summary.', 'outcomeSummary' => 'Approved outcome.', 'keyDetails' => ['Approved detail'],
            'clientIndustry' => 'Technology', 'confidential' => false, 'featuredPosition' => null,
        ]]);
        $this->getJson('/api/cases/featured-first')->assertJsonPath('data.value', '$100')->assertJsonPath('data.featuredPosition', 1);
        $page = $this->getJson('/api/cases?featured=1&perPage=1')->assertJsonPath('meta.total', 2);
        $this->getJson($page->json('links.next'))->assertJsonPath('data.0.id', 'featured-second');
    }

    public function test_invalid_filters_are_rejected_and_empty_collections_are_supported(): void
    {
        foreach (['/api/attorneys?perPage=101', '/api/attorneys?page=0', '/api/attorneys?q[]=x', '/api/attorneys?practiceArea[]=x', '/api/cases?category[]=x', '/api/cases?featured=yes', '/api/cases?page=-1'] as $url) {
            $this->getJson($url)->assertUnprocessable();
        }
        $this->getJson('/api/attorneys')->assertJsonCount(0, 'data')->assertJsonPath('meta.total', 0);
        $this->getJson('/api/cases')->assertJsonCount(0, 'data')->assertJsonPath('links.next', null);
    }

    public function test_demo_content_is_explicit_and_cannot_be_seeded_in_production(): void
    {
        $this->artisan('website-content:demo')->assertSuccessful();
        $this->getJson('/api/attorneys/demo-partner')->assertOk()->assertJsonPath('data.name', 'Demo Partner — Fictional Profile');
        $this->getJson('/api/cases/demo-governance-study')->assertOk();
        app()->instance('env', 'production');
        $this->artisan('website-content:demo')->assertFailed();
    }

    public function test_public_reads_are_rate_limited(): void
    {
        for ($i = 0; $i < 60; $i++) {
            $this->getJson('/api/attorneys')->assertOk();
        }
        $this->getJson('/api/cases')->assertStatus(429);
    }

    public function test_import_is_validated_idempotent_and_defaults_to_private(): void
    {
        $data = [
            'practiceAreas' => [['slug' => 'corporate-law', 'name' => 'Corporate Law']],
            'caseCategories' => [['slug' => 'corporate-governance', 'name' => 'Corporate Governance']],
            'attorneys' => [[
                'slug' => 'imported-partner', 'name' => 'Example Partner', 'role' => 'Partner', 'bio' => 'Biography.',
                'imageUrl' => 'https://example.com/profile.jpg', 'email' => 'partner@example.com', 'phone' => '+254712345678',
                'education' => [], 'admissions' => [], 'notableCases' => [], 'practiceAreas' => ['corporate-law'],
            ]],
            'cases' => [[
                'slug' => 'imported-case', 'title' => 'Example case', 'category' => 'corporate-governance',
                'description' => 'Approved summary.', 'outcomeSummary' => 'Approved outcome.', 'keyDetails' => [], 'clientIndustry' => 'Technology',
            ]],
        ];
        $file = tempnam(sys_get_temp_dir(), 'lexora-content-');
        try {
            file_put_contents($file, json_encode($data));
            $this->artisan('website-content:import', ['file' => $file])->assertSuccessful();
            $this->artisan('website-content:import', ['file' => $file])->assertSuccessful();
            $this->assertDatabaseCount('attorneys', 1);
            $this->assertDatabaseHas('attorneys', ['slug' => 'imported-partner', 'status' => 'draft']);
            $this->assertDatabaseHas('case_studies', ['slug' => 'imported-case', 'status' => 'draft', 'confidential' => true]);
            $this->getJson('/api/attorneys/imported-partner')->assertNotFound();
            $this->getJson('/api/cases/imported-case')->assertNotFound();

            $data['attorneys'][0]['status'] = 'published';
            $data['cases'][0]['status'] = 'published';
            $data['cases'][0]['confidential'] = false;
            file_put_contents($file, json_encode($data));
            $this->artisan('website-content:import', ['file' => $file])->assertSuccessful();
            $this->getJson('/api/attorneys/imported-partner')->assertOk()->assertJsonPath('data.practiceAreas.0', 'Corporate Law');
            $this->getJson('/api/cases/imported-case')->assertOk();

            $data['attorneys'][0]['education'] = ['bad' => 'Not an ordered list'];
            file_put_contents($file, json_encode($data));
            $this->artisan('website-content:import', ['file' => $file])->assertFailed();
            $this->assertDatabaseHas('attorneys', ['status' => 'published']);

            $data['attorneys'][0]['education'] = [];
            $data['attorneys'][0]['name'] = 'Should roll back';
            $data['cases'][0]['category'] = 'missing';
            file_put_contents($file, json_encode($data));
            $this->artisan('website-content:import', ['file' => $file])->assertFailed();
            $this->assertDatabaseHas('attorneys', ['name' => 'Example Partner']);
        } finally {
            unlink($file);
        }
    }
}
