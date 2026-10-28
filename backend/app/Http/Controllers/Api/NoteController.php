<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreNoteRequest;
use App\Http\Requests\UpdateNoteRequest;
use App\Http\Resources\NoteResource;
use App\Models\Note;
use OpenApi\Attributes as OA;

class NoteController extends Controller
{
    #[OA\Get(
        path: '/api/notes',
        summary: 'Список заметок',
        tags: ['Notes'],
        responses: [
            new OA\Response(response: 200, description: 'OK'),
        ]
    )]
    public function index()
    {
        return NoteResource::collection(Note::latest()->get());
    }

    #[OA\Post(
        path: '/api/notes',
        summary: 'Создать заметку',
        tags: ['Notes'],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['title'],
                properties: [
                    new OA\Property(property: 'title', type: 'string', example: 'Заголовок'),
                    new OA\Property(property: 'content', type: 'string', nullable: true, example: 'Текст'),
                ]
            )
        ),
        responses: [
            new OA\Response(response: 201, description: 'Создано'),
            new OA\Response(response: 422, description: 'Ошибка валидации'),
        ]
    )]
    public function store(StoreNoteRequest $request)
    {
        $note = Note::create($request->validated());

        return (new NoteResource($note))->response()->setStatusCode(201);
    }

    #[OA\Get(
        path: '/api/notes/{note}',
        summary: 'Одна заметка',
        tags: ['Notes'],
        parameters: [
            new OA\Parameter(name: 'note', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        responses: [
            new OA\Response(response: 200, description: 'OK'),
            new OA\Response(response: 404, description: 'Не найдено'),
        ]
    )]
    public function show(Note $note)
    {
        return new NoteResource($note);
    }

    #[OA\Put(
        path: '/api/notes/{note}',
        summary: 'Обновить заметку',
        tags: ['Notes'],
        parameters: [
            new OA\Parameter(name: 'note', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        requestBody: new OA\RequestBody(
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: 'title', type: 'string'),
                    new OA\Property(property: 'content', type: 'string', nullable: true),
                ]
            )
        ),
        responses: [
            new OA\Response(response: 200, description: 'OK'),
            new OA\Response(response: 422, description: 'Ошибка валидации'),
        ]
    )]
    public function update(UpdateNoteRequest $request, Note $note)
    {
        $note->update($request->validated());

        return new NoteResource($note);
    }

    #[OA\Delete(
        path: '/api/notes/{note}',
        summary: 'Удалить заметку',
        tags: ['Notes'],
        parameters: [
            new OA\Parameter(name: 'note', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        responses: [
            new OA\Response(response: 204, description: 'Удалено'),
            new OA\Response(response: 404, description: 'Не найдено'),
        ]
    )]
    public function destroy(Note $note)
    {
        $note->delete();

        return response()->noContent();
    }
}