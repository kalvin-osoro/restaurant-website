<?php

namespace Tests\Feature;

use App\Infrastructure\Persistence\Eloquent\Models\{Attorney, PracticeArea};
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TeamApiTest extends TestCase
{
    use RefreshDatabase;

    private function profile(string $slug, array $attributes = []): Attorney
    {
        return Attorney::create(array_merge([
            'slug' => $slug, 'name' => 'Example Partner', 'role' => 'Senior Partner',
            'bio' => 'Reviewed partner biography.', 'image_url' => 'https://example.com/partners/portrait.jpg',
            'email' => 'partner@example.com', 'phone' => '+254712345678',
            'education' => ['Reviewed qualification'], 'admissions' => ['Reviewed admission'],
            'notable_cases' => ['Approved public summary'], 'status' => 'published',
        ], $attributes));
    }

    public function test_team_cards_and_modal_receive_the_complete_public_profile(): void
    {
        $partner = $this->profile('example-partner');
        $area = PracticeArea::create(['slug' => 'corporate-law', 'name' => 'Corporate Law']);
        $partner->practiceAreas()->attach($area);
        $expected = [
            'id' => 'example-partner', 'name' => 'Example Partner', 'role' => 'Senior Partner',
            'bio' => 'Reviewed partner biography.', 'imageUrl' => 'https://example.com/partners/portrait.jpg',
            'email' => 'partner@example.com', 'phone' => '+254712345678', 'practiceAreas' => ['Corporate Law'],
            'education' => ['Reviewed qualification'], 'admissions' => ['Reviewed admission'],
            'notableCases' => ['Approved public summary'],
        ];
        $this->getJson('/api/v1/team')->assertOk()->assertJson(['data' => [$expected]])
            ->assertJsonCount(count($expected), 'data.0')->assertJsonPath('meta.total', 1);
        $this->getJson('/api/v1/team/example-partner')->assertOk()->assertExactJson(['data' => $expected]);
        $this->assertDatabaseHas('tbl_request_logs', ['route' => 'api/v1/team', 'status' => 200]);
        $this->assertDatabaseHas('tbl_request_logs', ['route' => 'api/v1/team/{slug}', 'status' => 200]);
    }

    public function test_persisted_image_and_biography_changes_appear_in_list_and_detail(): void
    {
        $partner = $this->profile('updated-partner');
        $this->getJson('/api/v1/team')->assertJsonPath('data.0.bio', 'Reviewed partner biography.');
        $partner->update([
            'bio' => 'Updated approved biography.',
            'image_url' => 'https://cdn.example.com/partners/updated.jpg',
        ]);
        foreach (['/api/v1/team', '/api/v1/team/updated-partner'] as $url) {
            $prefix = $url === '/api/v1/team' ? 'data.0' : 'data';
            $this->getJson($url)->assertOk()->assertJsonPath($prefix.'.bio', 'Updated approved biography.')
                ->assertJsonPath($prefix.'.imageUrl', 'https://cdn.example.com/partners/updated.jpg');
        }
        $partner->update(['status' => 'archived']);
        $this->getJson('/api/v1/team')->assertJsonCount(0, 'data');
        $this->getJson('/api/v1/team/updated-partner')->assertNotFound();
    }

    public function test_hidden_profiles_cannot_be_read_or_found_by_filters(): void
    {
        $this->profile('public');
        foreach (['draft', 'archived'] as $status) {
            $this->profile($status, ['status' => $status, 'name' => 'Unapproved Partner']);
            $this->getJson('/api/v1/team/'.$status)->assertNotFound();
        }
        $this->getJson('/api/v1/team')->assertJsonCount(1, 'data')->assertJsonPath('data.0.id', 'public');
        $this->getJson('/api/v1/team?q=Unapproved')->assertJsonCount(0, 'data');
        $this->getJson('/api/v1/team?'.http_build_query(['role' => 'Senior Partner']))->assertJsonCount(1, 'data');
        $this->getJson('/api/v1/team/missing')->assertNotFound();
    }

    public function test_partner_filters_preserve_order_and_filters_across_pages(): void
    {
        $area = PracticeArea::create(['slug' => 'litigation', 'name' => 'Litigation']);
        foreach (['b', 'a', 'first'] as $slug) {
            $partner = $this->profile($slug, ['display_order' => $slug === 'first' ? 0 : 10]);
            $partner->practiceAreas()->attach($area);
        }
        $this->profile('other-role', ['role' => 'Of Counsel']);
        $this->profile('other-practice');
        $query = http_build_query(['role' => 'Senior Partner', 'practiceArea' => 'litigation', 'q' => 'Example', 'perPage' => 1]);
        $first = $this->getJson('/api/v1/team?'.$query)->assertOk()
            ->assertJsonPath('data.0.id', 'first')->assertJsonPath('meta.total', 3);
        $second = $this->getJson($first->json('links.next'))->assertOk()
            ->assertJsonPath('data.0.id', 'a')->assertJsonPath('meta.total', 3);
        $this->getJson($second->json('links.next'))->assertJsonPath('data.0.id', 'b')->assertJsonPath('links.next', null);
        $this->getJson('/api/v1/attorneys?'.$query)->assertJsonPath('meta.total', 3);
    }

    public function test_invalid_filters_are_rejected_and_empty_team_is_safe(): void
    {
        foreach (['role=Unknown', 'role[]=Partner', 'perPage=101', 'page=0', 'q[]=name', 'practiceArea[]=area'] as $query) {
            $this->getJson('/api/v1/team?'.$query)->assertUnprocessable();
        }
        $this->getJson('/api/v1/team')->assertOk()->assertJsonCount(0, 'data')->assertJsonPath('meta.total', 0);
        $this->profile('empty-lists', ['education' => [], 'admissions' => [], 'notable_cases' => []]);
        $this->getJson('/api/v1/team/empty-lists')->assertOk()->assertJsonPath('data.practiceAreas', [])
            ->assertJsonPath('data.education', [])->assertJsonPath('data.admissions', [])->assertJsonPath('data.notableCases', []);
    }
}
