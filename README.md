# Notes Mini-Service

Мини-сервис заметок: REST API на Laravel 11 + UI на Vue 3, всё в Docker.

## Quick start

```bash
git clone <repo-url>
cd note-service
docker compose up -d --build
```

Открыть:

- UI: http://localhost:8080/ (редирект на `/app/`)
- API: http://localhost:8080/api/notes
- Swagger: http://localhost:8080/api/documentation

Первый запуск занимает ~2 минуты (сборка образа). Миграции и `APP_KEY` применяются автоматически через `docker-entrypoint.sh`.

## API

| Method | Path | Описание |
|--------|------|----------|
| GET    | /api/notes       | Список заметок |
| POST   | /api/notes       | Создать (`title` обязателен, `content` опционален) |
| GET    | /api/notes/{id}  | Одна заметка |
| PUT    | /api/notes/{id}  | Обновить (поля `sometimes`) |
| DELETE | /api/notes/{id}  | Удалить |

Пример:

```bash
curl -X POST http://localhost:8080/api/notes \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{"title":"Hello","content":"World"}'
```

Ошибки валидации: `422` с `errors.title`. Отсутствующая заметка: `404`.

## Документация

Swagger UI: http://localhost:8080/api/documentation

OpenAPI-спецификация генерируется из PHP-атрибутов `#[OA\...]` в контроллерах при старте контейнера (`php artisan l5-swagger:generate`). JSON лежит в `backend/storage/api-docs/api-docs.json`.

## Тесты

```bash
docker compose exec app php artisan test
```

Покрытие: 8 feature-тестов на все эндпоинты + валидацию + 404.

## Стек

- PHP 8.3 + Laravel 11
- SQLite (файл `backend/database/database.sqlite`, исключён из git)
- Vue 3 + TypeScript + Vite (собирается в `backend/public/app/`)
- Nginx (отдаёт статику и проксирует PHP на php-fpm)
- Docker Compose
- Swagger (OpenAPI 3.0) через `darkaonline/l5-swagger` — атрибуты `#[OA\...]`, генерация автоматически при старте

## Структура

```
backend/    Laravel API
frontend/   Vue UI
docker/     Dockerfile + nginx config
docker-compose.yml
```

## Что не реализовано (сознательно)

- Авторизация (Sanctum установлен, но не используется — по ТЗ не требуется)
- Пагинация в `GET /api/notes` — отдаём все заметки
- Rate limiting, кеширование
- MySQL в проде (в тесте SQLite ради простоты `docker compose up -d`)

## Пересборка фронта

```bash
docker run --rm -v "$PWD":/project -w /project/frontend node:22-alpine \
  sh -c "npm install && npm run build"
```

## Как это устроено

- `app` (php-fpm) собирается из образа `php:8.3-fpm-alpine` с кодом и `vendor/` внутри. Bind-mount кода **не используется** — это гарантирует, что контейнер воспроизводит именно то, что в git, а не локальный `vendor/`.
- `nginx` монтирует `./backend` только чтобы отдавать статику (`public/app/`) и `index.php`. PHP-запросы проксируются на `app:9000`.
- При старте `app` `docker-entrypoint.sh` идемпотентно: создаёт `.env`/`APP_KEY`, файл SQLite, применяет миграции, генерирует Swagger.
- Если нужно поменять код — `docker compose up -d --build` (пересборка образа), либо для активной разработки можно временно вернуть bind-mount и поставить `composer install` внутрь.