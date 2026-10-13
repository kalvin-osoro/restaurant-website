<?php

namespace App\Domain\WebsiteContent\Contracts;

use App\Domain\WebsiteContent\Data\ContentPage;

interface PublicContentRepository
{
    public function attorneys(array $filters): ContentPage;
    public function attorney(string $slug): ?array;
    public function practiceAreas(): array;
    public function cases(array $filters): ContentPage;
    public function caseStudy(string $slug): ?array;
    public function caseCategories(): array;
}
