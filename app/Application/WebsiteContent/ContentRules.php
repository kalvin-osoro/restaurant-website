<?php
namespace App\Application\WebsiteContent;
use Illuminate\Validation\Rule;
final class ContentRules
{
    public static function batch(): array
    {
        $slug = ['required', 'string', 'max:100', 'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/'];
        return [
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
        ];
    }
}
