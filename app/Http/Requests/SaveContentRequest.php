<?php

namespace App\Http\Requests;

use App\Application\WebsiteContent\ContentRules;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SaveContentRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        $type = $this->route('contentType');
        $prefix = $type.'.*.';
        $rules = [];
        foreach (ContentRules::batch() as $key => $value) {
            if (str_starts_with($key, $prefix)) {
                $rules[substr($key, strlen($prefix))] = array_values(array_filter($value, fn ($rule) => $rule !== 'distinct'));
            }
        }
        $tables = ['attorneys' => 'attorneys', 'cases' => 'case_studies', 'practiceAreas' => 'practice_areas', 'caseCategories' => 'case_categories'];
        $table = $tables[$type];
        $slug = $this->route('slug');
        $rules['slug'][] = Rule::unique($table, 'slug')->ignore($slug, 'slug');
        if ($slug !== null) {
            $rules['slug'][] = Rule::in([$slug]);
            $rules['slug'][] = Rule::exists($table, 'slug');
        }
        if (in_array($type, ['practiceAreas', 'caseCategories'], true)) {
            $rules['name'][] = Rule::unique($table, 'name')->ignore($slug, 'slug');
        }
        if ($type === 'attorneys') {
            $rules['practiceAreas.*'][] = 'distinct';
            $rules['practiceAreas.*'][] = Rule::exists('practice_areas', 'slug');
        }
        if ($type === 'cases') {
            $rules['category'][] = Rule::exists('case_categories', 'slug');
        }
        return $rules;
    }
}
