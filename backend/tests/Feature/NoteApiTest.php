<?php

namespace Tests\Feature;

use App\Models\Note;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NoteApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_list_notes(): void
    {
        Note::factory()->count(3)->create();

        $this->getJson('/api/notes')
            ->assertOk()
            ->assertJsonCount(3, 'data');
    }

    public function test_can_create_note(): void
    {
        $this->postJson('/api/notes', [
            'title'   => 'Test note',
            'content' => 'Body',
        ])
            ->assertCreated()
            ->assertJsonPath('data.title', 'Test note');

        $this->assertDatabaseHas('notes', ['title' => 'Test note']);
    }

    public function test_create_fails_with_empty_title(): void
    {
        $this->postJson('/api/notes', ['title' => ''])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('title');
    }

    public function test_can_show_note(): void
    {
        $note = Note::factory()->create();

        $this->getJson("/api/notes/{$note->id}")
            ->assertOk()
            ->assertJsonPath('data.id', $note->id);
    }

    public function test_show_returns_404_for_missing(): void
    {
        $this->getJson('/api/notes/999')
            ->assertNotFound();
    }

    public function test_can_update_note(): void
    {
        $note = Note::factory()->create(['title' => 'Old']);

        $this->putJson("/api/notes/{$note->id}", ['title' => 'New'])
            ->assertOk()
            ->assertJsonPath('data.title', 'New');
    }

    public function test_update_fails_with_empty_title(): void
    {
        $note = Note::factory()->create();

        $this->putJson("/api/notes/{$note->id}", ['title' => ''])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('title');
    }

    public function test_can_delete_note(): void
    {
        $note = Note::factory()->create();

        $this->deleteJson("/api/notes/{$note->id}")
            ->assertNoContent();

        $this->assertDatabaseMissing('notes', ['id' => $note->id]);
    }
}