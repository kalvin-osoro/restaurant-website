<?php

namespace App\Domain\WebsiteContent\Data;

final readonly class ContentPage
{
    public function __construct(
        public array $items,
        public int $total,
        public int $page,
        public int $perPage,
    ) {}
}
