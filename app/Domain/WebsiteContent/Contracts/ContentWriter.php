<?php
namespace App\Domain\WebsiteContent\Contracts;
interface ContentWriter
{
    public function save(array $data): void;
}
