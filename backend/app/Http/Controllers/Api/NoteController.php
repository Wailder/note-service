<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreNoteRequest;
use App\Http\Requests\UpdateNoteRequest;
use App\Http\Resources\NoteResource;
use App\Models\Note;
use Illuminate\Http\Request;

class NoteController extends Controller
{
    public function index()
    {
        return NoteResource::collection(Note::latest()->get());
    }

    public function store(StoreNoteRequest $request)
    {
        $note = Note::create($request->validated());

        return (new NoteResource($note))->response()->setStatusCode(201);
    }

    public function show(Note $note)
    {
        return new NoteResource($note);
    }

    public function update(UpdateNoteRequest $request, Note $note)
    {
        $note->update($request->validated());

        return new NoteResource($note);
    }

    public function destroy(Note $note)
    {
        $note->delete();

        return response()->noContent();
    }
}