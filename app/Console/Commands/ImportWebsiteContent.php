<?php

namespace App\Console\Commands;

use App\Application\WebsiteContent\ContentRules;
use App\Domain\WebsiteContent\Contracts\ContentWriter;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Validator;

class ImportWebsiteContent extends Command
{
    protected $signature = 'website-content:import {file : Path to a reviewed JSON content file}';
    protected $description = 'Validate and atomically upsert reviewed website content by public slug';

    public function handle(ContentWriter $writer): int
    {
        $file = $this->argument('file');
        if (!is_file($file) || !is_readable($file) || filesize($file) > 5 * 1024 * 1024) {
            $this->error('Provide a readable JSON file no larger than 5 MB.');
            return self::FAILURE;
        }
        try {
            $input = json_decode(file_get_contents($file), true, 512, JSON_THROW_ON_ERROR);
        } catch (\JsonException) {
            $this->error('The content file is not valid JSON.');
            return self::FAILURE;
        }
        if (!is_array($input) || array_is_list($input)) {
            $this->error('The content file must be a JSON object.');
            return self::FAILURE;
        }
        $validator = Validator::make($input, ContentRules::batch());
        if ($validator->fails()) {
            foreach ($validator->errors()->all() as $error) {
                $this->error($error);
            }
            return self::FAILURE;
        }
        $data = $validator->validated();
        try {
            $writer->save($data);
        } catch (\InvalidArgumentException $error) {
            $this->error($error->getMessage());
            return self::FAILURE;
        }
        $this->info('Website content imported. Only explicitly published, non-confidential content is public.');
        return self::SUCCESS;
    }
}
