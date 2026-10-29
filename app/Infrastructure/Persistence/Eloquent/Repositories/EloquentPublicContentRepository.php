<?php

namespace App\Infrastructure\Persistence\Eloquent\Repositories;

use App\Domain\WebsiteContent\Contracts\PublicContentRepository;
use App\Domain\WebsiteContent\Data\ContentPage;
use App\Infrastructure\Persistence\Eloquent\Models\{Attorney, CaseCategory, CaseStudy, PracticeArea};
use Illuminate\Database\Eloquent\Builder;

final class EloquentPublicContentRepository implements PublicContentRepository
{
    public function attorneys(array $filters): ContentPage
    {
        $query = Attorney::publiclyVisible()->with('practiceAreas');
        if (isset($filters['role'])) {
            $query->where('role', $filters['role']);
        }
        if (isset($filters['q'])) {
            // Use an explicit escape character, consistently on SQLite and MySQL.
            $term = '%'.str_replace(['!', '%', '_'], ['!!', '!%', '!_'], $filters['q']).'%';
            $query->where(fn (Builder $q) => $q->whereRaw("name LIKE ? ESCAPE '!'", [$term])
                ->orWhereRaw("bio LIKE ? ESCAPE '!'", [$term]));
        }
        if (isset($filters['practiceArea'])) {
            $query->whereHas('practiceAreas', fn (Builder $q) => $q->where('slug', $filters['practiceArea'])->orWhere('name', $filters['practiceArea']));
        }

        return $this->page($query->orderBy('display_order')->orderBy('slug'), $filters, $this->attorneyData(...));
    }

    public function attorney(string $slug): ?array
    {
        $record = Attorney::publiclyVisible()->with('practiceAreas')->where('slug', $slug)->first();
        return $record ? $this->attorneyData($record) : null;
    }

    public function practiceAreas(): array
    {
        return PracticeArea::whereHas('attorneys', fn (Builder $q) => $q->publiclyVisible())
            ->orderBy('display_order')->orderBy('slug')->get()
            ->map(fn (PracticeArea $area) => ['id' => $area->slug, 'name' => $area->name])->all();
    }

    public function cases(array $filters): ContentPage
    {
        $query = CaseStudy::publiclyVisible()->with('category');
        if (isset($filters['category'])) {
            $query->whereHas('category', fn (Builder $q) => $q->where('slug', $filters['category'])->orWhere('name', $filters['category']));
        }
        if (($filters['featured'] ?? false)) {
            $query->whereNotNull('featured_position');
        }
        $query->orderByRaw('CASE WHEN featured_position IS NULL THEN 1 ELSE 0 END')
            ->orderBy('featured_position')->orderBy('display_order')->orderBy('slug');

        return $this->page($query, $filters, $this->caseData(...));
    }

    public function caseStudy(string $slug): ?array
    {
        $record = CaseStudy::publiclyVisible()->with('category')->where('slug', $slug)->first();
        return $record ? $this->caseData($record) : null;
    }

    public function caseCategories(): array
    {
        return CaseCategory::whereHas('caseStudies', fn (Builder $q) => $q->publiclyVisible())
            ->orderBy('display_order')->orderBy('slug')->get()
            ->map(fn (CaseCategory $category) => ['id' => $category->slug, 'name' => $category->name])->all();
    }

    private function page(Builder $query, array $filters, callable $map): ContentPage
    {
        $page = $query->paginate($filters['perPage'] ?? 20, ['*'], 'page', $filters['page'] ?? 1);
        return new ContentPage($page->getCollection()->map($map)->all(), $page->total(), $page->currentPage(), $page->perPage());
    }

    private function attorneyData(Attorney $record): array
    {
        return [
            'id' => $record->slug, 'name' => $record->name, 'role' => $record->role,
            'bio' => $record->bio, 'practiceAreas' => $record->practiceAreas->pluck('name')->all(),
            'imageUrl' => $record->image_url, 'email' => $record->email, 'phone' => $record->phone,
            'education' => $record->education, 'admissions' => $record->admissions,
            'notableCases' => $record->notable_cases,
        ];
    }

    private function caseData(CaseStudy $record): array
    {
        return [
            'id' => $record->slug, 'title' => $record->title, 'category' => $record->category->name,
            'description' => $record->description, 'value' => $record->value,
            'timeline' => $record->timeline, 'jurisdiction' => $record->jurisdiction,
            'outcomeSummary' => $record->outcome_summary, 'keyDetails' => $record->key_details,
            'imageUrl' => $record->image_url, 'clientIndustry' => $record->client_industry,
            'confidential' => false, 'featuredPosition' => $record->featured_position,
        ];
    }
}
