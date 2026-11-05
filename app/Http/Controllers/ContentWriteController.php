<?php

namespace App\Http\Controllers;

use App\Application\WebsiteContent\Actions\SaveContent;
use App\Http\Requests\SaveContentRequest;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;

abstract class ContentWriteController extends Controller
{
    protected function persist(SaveContentRequest $request, SaveContent $save): JsonResponse
    {
        $record = $request->validated();
        try {
            $save->execute($request->contentType(), $record);
        } catch (\InvalidArgumentException $error) {
            throw ValidationException::withMessages(['relationships' => $error->getMessage()]);
        } catch (UniqueConstraintViolationException) {
            abort(409, 'A record with this slug or name already exists.');
        }
        return result($request->route('slug') === null ? 'Content created' : 'Content updated', ['id' => $record['slug']], $request->route('slug') === null ? 201 : 200);
    }
}
