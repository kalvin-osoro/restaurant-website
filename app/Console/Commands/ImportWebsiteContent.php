<?php

namespace App\Console\Commands;

use App\Infrastructure\Persistence\Eloquent\Models\{Attorney, CaseCategory, CaseStudy, PracticeArea};
use Illuminate\Console\Command;
use Illuminate\Support\Facades\{DB, Validator};
use Illuminate\Validation\Rule;

class ImportWebsiteContent extends Command
{
    protected $signature = 'website-content:import {file : Path to a reviewed JSON content file}';
    protected $description = 'Validate and atomically upsert reviewed website content by public slug';

    public function handle(): int
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
        $slug = ['required', 'string', 'max:100', 'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/'];
        $validator = Validator::make($input, [
            'practiceAreas' => ['sometimes', 'array', 'list', 'max:100'],
            'practiceAreas.*' => ['array:slug,name,displayOrder'],
            'practiceAreas.*.slug' => [...$slug, 'distinct'],
            'practiceAreas.*.name' => ['required', 'string', 'max:100', 'distinct'],
            'practiceAreas.*.displayOrder' => ['sometimes', 'integer', 'min:0', 'max:100000'],
            'caseCategories' => ['sometimes', 'array', 'list', 'max:100'],
            'caseCategories.*' => ['array:slug,name,displayOrder'],
            'caseCategories.*.slug' => [...$slug, 'distinct'],
            'caseCategories.*.name' => ['required', Rule::in(['Mergers & Acquisitions', 'Commercial Litigation', 'Intellectual Property', 'Real Estate & Zoning', 'Corporate Governance']), 'distinct'],
            'caseCategories.*.displayOrder' => ['sometimes', 'integer', 'min:0', 'max:100000'],
            'attorneys' => ['sometimes', 'array', 'list', 'max:1000'],
            'attorneys.*' => ['array:slug,name,role,bio,imageUrl,email,phone,education,admissions,notableCases,practiceAreas,status,displayOrder'],
            'attorneys.*.slug' => [...$slug, 'distinct'],
            'attorneys.*.name' => ['required', 'string', 'max:255'],
            'attorneys.*.role' => ['required', Rule::in(['Managing Partner', 'Senior Partner', 'Partner', 'Of Counsel'])],
            'attorneys.*.bio' => ['required', 'string', 'max:20000'],
            'attorneys.*.imageUrl' => ['required', 'url:http,https', 'max:2048'],
            'attorneys.*.email' => ['required', 'email', 'max:255'],
            'attorneys.*.phone' => ['required', 'string', 'max:100'],
            'attorneys.*.education' => ['present', 'array', 'list', 'max:50'],
            'attorneys.*.education.*' => ['required', 'string', 'max:2000'],
            'attorneys.*.admissions' => ['present', 'array', 'list', 'max:50'],
            'attorneys.*.admissions.*' => ['required', 'string', 'max:2000'],
            'attorneys.*.notableCases' => ['present', 'array', 'list', 'max:50'],
            'attorneys.*.notableCases.*' => ['required', 'string', 'max:2000'],
            'attorneys.*.practiceAreas' => ['present', 'array', 'list', 'max:50'],
            'attorneys.*.practiceAreas.*' => $slug,
            'attorneys.*.status' => ['sometimes', Rule::in(['draft', 'published', 'archived'])],
            'attorneys.*.displayOrder' => ['sometimes', 'integer', 'min:0', 'max:100000'],
            'cases' => ['sometimes', 'array', 'list', 'max:1000'],
            'cases.*' => ['array:slug,title,category,description,value,timeline,jurisdiction,outcomeSummary,keyDetails,imageUrl,clientIndustry,confidential,status,displayOrder,featuredPosition'],
            'cases.*.slug' => [...$slug, 'distinct'],
            'cases.*.title' => ['required', 'string', 'max:255'],
            'cases.*.category' => $slug,
            'cases.*.description' => ['required', 'string', 'max:20000'],
            'cases.*.value' => ['sometimes', 'nullable', 'string', 'max:255'],
            'cases.*.timeline' => ['sometimes', 'nullable', 'string', 'max:255'],
            'cases.*.jurisdiction' => ['sometimes', 'nullable', 'string', 'max:255'],
            'cases.*.outcomeSummary' => ['required', 'string', 'max:20000'],
            'cases.*.keyDetails' => ['present', 'array', 'list', 'max:50'],
            'cases.*.keyDetails.*' => ['required', 'string', 'max:2000'],
            'cases.*.imageUrl' => ['sometimes', 'nullable', 'url:http,https', 'max:2048'],
            'cases.*.clientIndustry' => ['required', 'string', 'max:255'],
            'cases.*.confidential' => ['sometimes', 'boolean'],
            'cases.*.status' => ['sometimes', Rule::in(['draft', 'published', 'archived'])],
            'cases.*.displayOrder' => ['sometimes', 'integer', 'min:0', 'max:100000'],
            'cases.*.featuredPosition' => ['sometimes', 'nullable', 'integer', 'min:1', 'max:100000'],
        ]);
        if ($validator->fails()) {
            foreach ($validator->errors()->all() as $error) {
                $this->error($error);
            }
            return self::FAILURE;
        }
        $data = $validator->validated();
        try {
            DB::transaction(function () use ($data) {
                foreach ($data['practiceAreas'] ?? [] as $area) {
                    PracticeArea::updateOrCreate(['slug' => $area['slug']], ['name' => $area['name'], 'display_order' => $area['displayOrder'] ?? 0]);
                }
                foreach ($data['caseCategories'] ?? [] as $category) {
                    CaseCategory::updateOrCreate(['slug' => $category['slug']], ['name' => $category['name'], 'display_order' => $category['displayOrder'] ?? 0]);
                }
                foreach ($data['attorneys'] ?? [] as $record) {
                    $areas = PracticeArea::whereIn('slug', array_unique($record['practiceAreas']))->pluck('id');
                    if ($areas->count() !== count(array_unique($record['practiceAreas']))) {
                        throw new \InvalidArgumentException('An attorney references an unknown practice-area slug.');
                    }
                    $attorney = Attorney::updateOrCreate(['slug' => $record['slug']], [
                        'name' => $record['name'], 'role' => $record['role'], 'bio' => $record['bio'],
                        'image_url' => $record['imageUrl'], 'email' => $record['email'], 'phone' => $record['phone'],
                        'education' => $record['education'], 'admissions' => $record['admissions'], 'notable_cases' => $record['notableCases'],
                        'status' => $record['status'] ?? 'draft', 'display_order' => $record['displayOrder'] ?? 0,
                    ]);
                    $attorney->practiceAreas()->sync($areas->all());
                }
                foreach ($data['cases'] ?? [] as $record) {
                    $category = CaseCategory::where('slug', $record['category'])->first();
                    if (!$category) {
                        throw new \InvalidArgumentException('A case references an unknown category slug.');
                    }
                    CaseStudy::updateOrCreate(['slug' => $record['slug']], [
                        'case_category_id' => $category->id, 'title' => $record['title'], 'description' => $record['description'],
                        'value' => $record['value'] ?? null, 'timeline' => $record['timeline'] ?? null,
                        'jurisdiction' => $record['jurisdiction'] ?? null, 'outcome_summary' => $record['outcomeSummary'],
                        'key_details' => $record['keyDetails'], 'image_url' => $record['imageUrl'] ?? null,
                        'client_industry' => $record['clientIndustry'], 'confidential' => $record['confidential'] ?? true,
                        'status' => $record['status'] ?? 'draft', 'display_order' => $record['displayOrder'] ?? 0,
                        'featured_position' => $record['featuredPosition'] ?? null,
                    ]);
                }
            });
        } catch (\InvalidArgumentException $error) {
            $this->error($error->getMessage());
            return self::FAILURE;
        }
        $this->info('Website content imported. Only explicitly published, non-confidential content is public.');
        return self::SUCCESS;
    }
}
