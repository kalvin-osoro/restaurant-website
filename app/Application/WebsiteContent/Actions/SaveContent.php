<?php

namespace App\Application\WebsiteContent\Actions;

use App\Domain\WebsiteContent\Contracts\ContentWriter;

final class SaveContent
{
    public function __construct(private ContentWriter $writer) {}

    public function execute(string $type, array $record): void
    {
        $this->writer->save([$type => [$record]]);
    }
}
