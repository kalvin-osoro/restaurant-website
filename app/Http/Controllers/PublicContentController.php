<?php

namespace App\Http\Controllers;

use App\Application\WebsiteContent\Actions\ReadPublicContent;
use App\Domain\WebsiteContent\Data\ContentPage;
use App\Http\Requests\{ListAttorneysRequest, ListCasesRequest};
use App\Http\Resources\{AttorneyResource, CaseStudyResource};
use Illuminate\Http\{JsonResponse, Request};

class PublicContentController extends Controller
{
    public function attorneys(ListAttorneysRequest $request, ReadPublicContent $read): JsonResponse
    {
        return $this->page($request, $read->attorneys($request->validated()), AttorneyResource::class);
    }

    public function attorney(string $slug, ReadPublicContent $read): AttorneyResource
    {
        $record = $read->attorney($slug);
        abort_if($record === null, 404);
        return new AttorneyResource($record);
    }

    public function practiceAreas(ReadPublicContent $read): JsonResponse
    {
        return response()->json(['data' => $read->practiceAreas()]);
    }

    public function cases(ListCasesRequest $request, ReadPublicContent $read): JsonResponse
    {
        return $this->page($request, $read->cases($request->validated()), CaseStudyResource::class);
    }

    public function caseStudy(string $slug, ReadPublicContent $read): CaseStudyResource
    {
        $record = $read->caseStudy($slug);
        abort_if($record === null, 404);
        return new CaseStudyResource($record);
    }

    public function caseCategories(ReadPublicContent $read): JsonResponse
    {
        return response()->json(['data' => $read->caseCategories()]);
    }

    private function page(Request $request, ContentPage $page, string $resource): JsonResponse
    {
        $lastPage = max(1, (int) ceil($page->total / $page->perPage));
        $url = fn (int $number) => $request->url().'?'.http_build_query(array_merge($request->only(['q', 'practiceArea', 'category', 'featured']), ['perPage' => $page->perPage, 'page' => $number]));
        return response()->json([
            'data' => $resource::collection($page->items)->resolve($request),
            'links' => ['first' => $url(1), 'last' => $url($lastPage), 'prev' => $page->page > 1 ? $url($page->page - 1) : null, 'next' => $page->page < $lastPage ? $url($page->page + 1) : null],
            'meta' => ['current_page' => $page->page, 'last_page' => $lastPage, 'per_page' => $page->perPage, 'total' => $page->total],
        ]);
    }
}
