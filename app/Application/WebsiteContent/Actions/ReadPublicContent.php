<?php

namespace App\Application\WebsiteContent\Actions;

use App\Domain\WebsiteContent\Contracts\PublicContentRepository;
use App\Domain\WebsiteContent\Data\ContentPage;

final readonly class ReadPublicContent
{
    public function __construct(private PublicContentRepository $repository) {}

    public function attorneys(array $filters): ContentPage { return $this->repository->attorneys($filters); }
    public function attorney(string $slug): ?array { return $this->repository->attorney($slug); }
    public function practiceAreas(): array { return $this->repository->practiceAreas(); }
    public function cases(array $filters): ContentPage { return $this->repository->cases($filters); }
    public function caseStudy(string $slug): ?array { return $this->repository->caseStudy($slug); }
    public function caseCategories(): array { return $this->repository->caseCategories(); }
}
