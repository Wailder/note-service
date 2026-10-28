<script lang="ts">
type Note = {
  id: number
  title: string
  content: string | null
  created_at: string
  updated_at: string
}

type FormState = {
  id: number | null
  title: string
  content: string
}

const API = '/api/notes'

export default {
  data() {
    return {
      notes: [] as Note[],
      form: { id: null, title: '', content: '' } as FormState,
      error: '' as string,
      loading: false,
    }
  },
  async mounted() {
    await this.loadNotes()
  },
  methods: {
    async loadNotes() {
      this.loading = true
      this.error = ''
      try {
        const res = await fetch(API)
        const json = await res.json()
        this.notes = json.data
      } catch (e: any) {
        this.error = 'Не удалось загрузить заметки'
      } finally {
        this.loading = false
      }
    },
    async save() {
      if (!this.form.title.trim()) {
        this.error = 'Заголовок обязателен'
        return
      }
      this.error = ''
      const isEdit = this.form.id !== null
      const url = isEdit ? `${API}/${this.form.id}` : API
      const method = isEdit ? 'PUT' : 'POST'
      try {
        const res = await fetch(url, {
          method,
          headers: {
            'Content-Type': 'application/json',
            Accept: 'application/json',
          },
          body: JSON.stringify({
            title: this.form.title,
            content: this.form.content || null,
          }),
        })
        if (!res.ok) {
          const err = await res.json()
          this.error = err.message ?? 'Ошибка сохранения'
          return
        }
        this.resetForm()
        await this.loadNotes()
      } catch {
        this.error = 'Сетевая ошибка'
      }
    },
    edit(note: Note) {
      this.form = { id: note.id, title: note.title, content: note.content ?? '' }
      this.error = ''
    },
    async remove(note: Note) {
      if (!confirm(`Удалить «${note.title}»?`)) return
      try {
        const res = await fetch(`${API}/${note.id}`, { method: 'DELETE' })
        if (!res.ok) {
          this.error = 'Не удалось удалить'
          return
        }
        await this.loadNotes()
      } catch {
        this.error = 'Сетевая ошибка'
      }
    },
    resetForm() {
      this.form = { id: null, title: '', content: '' }
    },
  },
}
</script>

<template>
  <div class="app">
    <header>
      <h1>Заметки</h1>
      <p class="subtitle">Мини-сервис заметок · Laravel + Vue</p>
    </header>

    <section class="editor">
      <h2>{{ form.id ? 'Редактировать заметку' : 'Новая заметка' }}</h2>
      <input
        v-model="form.title"
        type="text"
        placeholder="Заголовок"
        maxlength="255"
      />
      <textarea
        v-model="form.content"
        placeholder="Текст заметки (необязательно)"
        rows="4"
      ></textarea>
      <div class="actions">
        <button @click="save" :disabled="!form.title.trim()">
          {{ form.id ? 'Сохранить' : 'Создать' }}
        </button>
        <button v-if="form.id" class="secondary" @click="resetForm">Отмена</button>
      </div>
      <p v-if="error" class="error">{{ error }}</p>
    </section>

    <section class="list">
      <h2>Все заметки <span v-if="loading">(загрузка…)</span></h2>
      <p v-if="!notes.length && !loading" class="empty">Пока пусто — создай первую.</p>
      <article v-for="note in notes" :key="note.id" class="card">
        <header>
          <h3>{{ note.title }}</h3>
          <small>{{ new Date(note.updated_at).toLocaleString() }}</small>
        </header>
        <p v-if="note.content" class="content">{{ note.content }}</p>
        <div class="actions">
          <button class="secondary" @click="edit(note)">Редактировать</button>
          <button class="danger" @click="remove(note)">Удалить</button>
        </div>
      </article>
    </section>
  </div>
</template>

<style>
* { box-sizing: border-box; }
body {
  margin: 0;
  font-family: system-ui, -apple-system, 'Segoe UI', Roboto, sans-serif;
  background: #f4f5f7;
  color: #1f2933;
}
.app {
  max-width: 760px;
  margin: 0 auto;
  padding: 32px 20px 60px;
}
header h1 { margin: 0 0 4px; font-size: 28px; }
.subtitle { margin: 0 0 24px; color: #667; font-size: 14px; }
section { background: #fff; border-radius: 10px; padding: 20px; margin-bottom: 20px; box-shadow: 0 1px 3px rgba(0,0,0,.06); }
section h2 { margin: 0 0 12px; font-size: 18px; }
input, textarea {
  width: 100%;
  padding: 10px 12px;
  margin-bottom: 10px;
  border: 1px solid #d0d5dd;
  border-radius: 6px;
  font: inherit;
  font-size: 14px;
}
textarea { resize: vertical; }
.actions { display: flex; gap: 8px; }
button {
  padding: 8px 14px;
  border: 0;
  border-radius: 6px;
  background: #2563eb;
  color: #fff;
  font: inherit;
  font-size: 14px;
  cursor: pointer;
}
button:disabled { opacity: .5; cursor: not-allowed; }
button.secondary { background: #e4e7eb; color: #1f2933; }
button.danger { background: #dc2626; }
.error { color: #dc2626; margin: 8px 0 0; font-size: 13px; }
.empty { color: #889; font-size: 14px; }
.card { border: 1px solid #e4e7eb; border-radius: 8px; padding: 14px; margin-bottom: 10px; }
.card header { display: flex; justify-content: space-between; align-items: baseline; gap: 12px; }
.card h3 { margin: 0; font-size: 16px; }
.card small { color: #889; font-size: 12px; white-space: nowrap; }
.card .content { margin: 8px 0 12px; white-space: pre-wrap; font-size: 14px; color: #374151; }
</style>