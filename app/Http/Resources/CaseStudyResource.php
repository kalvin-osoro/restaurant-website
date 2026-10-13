<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CaseStudyResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $data = array_intersect_key($this->resource, array_flip([
            'id', 'title', 'category', 'description', 'value', 'timeline', 'jurisdiction',
            'outcomeSummary', 'keyDetails', 'imageUrl', 'clientIndustry', 'confidential', 'featuredPosition',
        ]));
        foreach (['value', 'timeline', 'jurisdiction', 'imageUrl'] as $optional) {
            if ($data[$optional] === null) {
                unset($data[$optional]);
            }
        }
        return $data;
    }
}
