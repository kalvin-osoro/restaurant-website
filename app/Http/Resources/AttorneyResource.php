<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AttorneyResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return array_intersect_key($this->resource, array_flip([
            'id', 'name', 'role', 'bio', 'practiceAreas', 'imageUrl', 'email', 'phone',
            'education', 'admissions', 'notableCases',
        ]));
    }
}
