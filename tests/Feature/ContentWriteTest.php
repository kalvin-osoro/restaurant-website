<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ContentWriteTest extends TestCase
{
    use RefreshDatabase;

    private function credentials(): void
    {
        config(['website-content.write_token' => str_repeat('x', 40)]);
        $this->withToken(str_repeat('x', 40));
    }

    private function profile(): array
    {
        return [
            'slug' => 'example-partner', 'name' => 'Example Partner', 'role' => 'Partner',
            'bio' => 'Approved biography.', 'imageUrl' => 'https://example.com/partner.jpg',
            'email' => 'partner@example.com', 'phone' => '+254712345678',
            'education' => ['LLB'], 'admissions' => [], 'notableCases' => [], 'practiceAreas' => ['corporate-law'],
        ];
    }

    public function test_writes_fail_closed_and_reads_remain_public(): void
    {
        config(['website-content.write_token' => null]);
        $this->postJson('/api/v1/team', $this->profile())->assertStatus(503);
        config(['website-content.write_token' => str_repeat('x', 40)]);
        $this->postJson('/api/v1/team', $this->profile())->assertUnauthorized();
        $this->withToken('wrong')->postJson('/api/v1/case-categories', [])->assertUnauthorized();
        $this->assertDatabaseCount('attorneys', 0);
        $this->getJson('/api/v1/team')->assertOk();
        $this->getJson('/api/team')->assertNotFound();
    }

    public function test_team_alias_creates_and_detail_post_replaces_persisted_profile(): void
    {
        $this->credentials();
        $this->postJson('/api/v1/practice-areas', ['slug' => 'corporate-law', 'name' => 'Corporate Law'])->assertCreated();
        $record = $this->profile();
        $this->postJson('/api/v1/team', $record)->assertCreated()->assertJsonPath('data.id', $record['slug']);
        $this->assertDatabaseHas('attorneys', ['slug' => $record['slug'], 'status' => 'draft']);
        $this->assertDatabaseCount('attorney_practice_area', 1);
        $this->getJson('/api/v1/team/'.$record['slug'])->assertNotFound();
        $record['status'] = 'published';
        $record['bio'] = 'Updated biography.';
        $this->postJson('/api/v1/attorneys/'.$record['slug'], $record)->assertOk();
        $this->getJson('/api/v1/team/'.$record['slug'])->assertJsonPath('data.bio', 'Updated biography.')->assertJsonPath('data.practiceAreas.0', 'Corporate Law');
        $this->getJson('/api/v1/practice-areas')->assertJsonCount(1, 'data');
        $this->postJson('/api/v1/attorneys', $record)->assertUnprocessable()->assertJsonValidationErrors('slug');
        $record['practiceAreas'] = [];
        $this->postJson('/api/v1/team/'.$record['slug'], $record)->assertOk();
        $this->assertDatabaseCount('attorney_practice_area', 0);
        $record['slug'] = 'another';
        $this->postJson('/api/v1/team/example-partner', $record)->assertUnprocessable();
        $this->assertDatabaseCount('attorneys', 1);
    }

    public function test_cases_persist_with_private_defaults_and_can_be_published(): void
    {
        $this->credentials();
        $this->postJson('/api/v1/case-categories', ['slug' => 'governance', 'name' => 'Corporate Governance'])->assertCreated();
        $record = ['slug' => 'example-case', 'title' => 'Example case', 'category' => 'governance', 'description' => 'Approved description.', 'outcomeSummary' => 'Approved outcome.', 'keyDetails' => ['Detail'], 'clientIndustry' => 'Technology'];
        $this->postJson('/api/v1/cases', $record)->assertCreated();
        $this->assertDatabaseHas('case_studies', ['slug' => 'example-case', 'status' => 'draft', 'confidential' => true]);
        $this->getJson('/api/v1/cases/example-case')->assertNotFound();
        $record += ['status' => 'published', 'confidential' => false, 'featuredPosition' => 1];
        $this->postJson('/api/v1/cases/example-case', $record)->assertOk();
        $this->getJson('/api/v1/cases/example-case')->assertJsonPath('data.category', 'Corporate Governance')->assertJsonPath('data.featuredPosition', 1);
        $this->getJson('/api/v1/case-categories')->assertJsonCount(1, 'data');
        $this->postJson('/api/v1/cases', $record)->assertUnprocessable();
    }

    public function test_invalid_content_and_relationships_do_not_write_partial_records(): void
    {
        $this->credentials();
        $this->postJson('/api/v1/team', $this->profile())->assertUnprocessable()->assertJsonValidationErrors('practiceAreas.0');
        $record = $this->profile();
        $record['practiceAreas'] = [];
        $record['imageUrl'] = 'javascript:alert(1)';
        $this->postJson('/api/v1/attorneys', $record)->assertUnprocessable()->assertJsonValidationErrors('imageUrl');
        $this->postJson('/api/v1/cases', ['slug' => 'bad'])->assertUnprocessable();
        $this->assertDatabaseCount('attorneys', 0);
        $this->assertDatabaseCount('case_studies', 0);
        $this->postJson('/api/v1/practice-areas', ['slug' => 'law', 'name' => 'Law'])->assertCreated();
        $this->postJson('/api/v1/practice-areas', ['slug' => 'other-law', 'name' => 'Law'])->assertUnprocessable()->assertJsonValidationErrors('name');
    }
}
