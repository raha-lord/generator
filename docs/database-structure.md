# Структура базы данных

## Общая информация

- **СУБД:** PostgreSQL 15+
- **Кодировка:** UTF-8
- **Часовой пояс:** UTC
- **ORM:** Eloquent (Laravel)

---

## 1. Таблица: users

Хранит информацию о зарегистрированных пользователях.

### Структура

```sql
CREATE TABLE users (
    id                  BIGSERIAL PRIMARY KEY,
    name                VARCHAR(255) NOT NULL,
    email               VARCHAR(255) NOT NULL UNIQUE,
    email_verified_at   TIMESTAMP NULL,
    password            VARCHAR(255) NOT NULL,
    remember_token      VARCHAR(100) NULL,
    created_at          TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at          TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
);

CREATE INDEX idx_users_email ON users(email);
CREATE INDEX idx_users_created_at ON users(created_at);
```

### Поля

| Поле | Тип | Описание |
|------|-----|----------|
| `id` | BIGSERIAL | Уникальный идентификатор |
| `name` | VARCHAR(255) | Имя пользователя |
| `email` | VARCHAR(255) | Email (уникальный) |
| `email_verified_at` | TIMESTAMP | Дата верификации email (NULL = не верифицирован) |
| `password` | VARCHAR(255) | Bcrypt хэш пароля |
| `remember_token` | VARCHAR(100) | Токен для "Запомнить меня" |
| `created_at` | TIMESTAMP | Дата регистрации |
| `updated_at` | TIMESTAMP | Дата последнего обновления |

### Индексы

- `PRIMARY KEY` на `id`
- `UNIQUE` на `email`
- `INDEX` на `email` (для быстрого поиска)
- `INDEX` на `created_at` (для сортировки)

---

## 2. Таблица: balances

Хранит баланс кредитов каждого пользователя.

### Структура

```sql
CREATE TABLE balances (
    id          BIGSERIAL PRIMARY KEY,
    user_id     BIGINT NOT NULL,
    credits     INTEGER NOT NULL DEFAULT 1000,
    created_at  TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at  TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT fk_balances_user
        FOREIGN KEY (user_id)
        REFERENCES users(id)
        ON DELETE CASCADE
);

CREATE UNIQUE INDEX idx_balances_user_id ON balances(user_id);
```

### Поля

| Поле | Тип | Описание |
|------|-----|----------|
| `id` | BIGSERIAL | Уникальный идентификатор |
| `user_id` | BIGINT | ID пользователя (FK) |
| `credits` | INTEGER | Текущий баланс кредитов |
| `created_at` | TIMESTAMP | Дата создания записи |
| `updated_at` | TIMESTAMP | Дата последнего обновления |

### Связи

- **users** (1:1) — один пользователь = один баланс

### Бизнес-правила

- При регистрации пользователя автоматически создается запись с `credits = 1000`
- `credits` не может быть отрицательным
- Обновление через транзакции для консистентности

---

## 3. Таблица: generations

Основная таблица для всех типов генераций (полиморфная связь).

### Структура

```sql
CREATE TABLE generations (
    id                  BIGSERIAL PRIMARY KEY,
    uuid                UUID NOT NULL UNIQUE DEFAULT gen_random_uuid(),
    user_id             BIGINT NOT NULL,
    generatable_type    VARCHAR(255) NOT NULL,
    generatable_id      BIGINT NOT NULL,
    prompt              TEXT NOT NULL,
    status              VARCHAR(50) NOT NULL DEFAULT 'pending',
    moderation_status   VARCHAR(50) NOT NULL DEFAULT 'approved',
    cost                INTEGER NOT NULL,
    file_path           VARCHAR(500) NOT NULL,
    public_url          VARCHAR(500) NULL,
    error_message       TEXT NULL,
    created_at          TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at          TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT fk_generations_user
        FOREIGN KEY (user_id)
        REFERENCES users(id)
        ON DELETE CASCADE,

    CONSTRAINT chk_status
        CHECK (status IN ('pending', 'processing', 'completed', 'failed')),

    CONSTRAINT chk_moderation_status
        CHECK (moderation_status IN ('pending', 'approved', 'rejected'))
);

CREATE INDEX idx_generations_user_id ON generations(user_id);
CREATE UNIQUE INDEX idx_generations_uuid ON generations(uuid);
CREATE INDEX idx_generations_status ON generations(status);
CREATE INDEX idx_generations_created_at ON generations(created_at DESC);
CREATE INDEX idx_generations_generatable ON generations(generatable_type, generatable_id);
```

### Поля

| Поле | Тип | Описание |
|------|-----|----------|
| `id` | BIGSERIAL | Уникальный идентификатор |
| `uuid` | UUID | UUID для публичных ссылок |
| `user_id` | BIGINT | ID пользователя (FK) |
| `generatable_type` | VARCHAR(255) | Тип генерации (App\Models\Infographic) |
| `generatable_id` | BIGINT | ID связанной записи |
| `prompt` | TEXT | Промт пользователя |
| `status` | VARCHAR(50) | Статус генерации |
| `moderation_status` | VARCHAR(50) | Статус модерации |
| `cost` | INTEGER | Стоимость генерации (кредиты) |
| `file_path` | VARCHAR(500) | Путь к файлу в storage |
| `public_url` | VARCHAR(500) | Публичный URL (опционально) |
| `error_message` | TEXT | Сообщение об ошибке (если есть) |
| `created_at` | TIMESTAMP | Дата создания |
| `updated_at` | TIMESTAMP | Дата обновления |

### Enum значения

**status:**
- `pending` — ожидает обработки
- `processing` — в процессе генерации
- `completed` — успешно завершена
- `failed` — ошибка

**moderation_status:**
- `pending` — ожидает модерации
- `approved` — одобрено (по умолчанию в МВП)
- `rejected` — отклонено

### Связи

- **users** (N:1) — много генераций у одного пользователя
- **generatable** (полиморфная) — связь с конкретным типом (Infographic, Text, etc.)

### Индексы

- `PRIMARY KEY` на `id`
- `UNIQUE` на `uuid`
- `INDEX` на `user_id` (для выборки истории пользователя)
- `INDEX` на `status` (для фильтрации)
- `INDEX` на `created_at DESC` (для сортировки по дате)
- `INDEX` на `(generatable_type, generatable_id)` (для полиморфной связи)

---

## 4. Таблица: infographics

Специфичные данные для генерации инфографики.

### Структура

```sql
CREATE TABLE infographics (
    id              BIGSERIAL PRIMARY KEY,
    generation_id   BIGINT NOT NULL,
    width           INTEGER NULL,
    height          INTEGER NULL,
    format          VARCHAR(50) NOT NULL DEFAULT 'png',
    metadata        JSONB NULL,
    created_at      TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at      TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT fk_infographics_generation
        FOREIGN KEY (generation_id)
        REFERENCES generations(id)
        ON DELETE CASCADE
);

CREATE UNIQUE INDEX idx_infographics_generation_id ON infographics(generation_id);
```

### Поля

| Поле | Тип | Описание |
|------|-----|----------|
| `id` | BIGSERIAL | Уникальный идентификатор |
| `generation_id` | BIGINT | ID генерации (FK) |
| `width` | INTEGER | Ширина изображения (px) |
| `height` | INTEGER | Высота изображения (px) |
| `format` | VARCHAR(50) | Формат файла (png, jpg, webp) |
| `metadata` | JSONB | Дополнительные параметры (JSON) |
| `created_at` | TIMESTAMP | Дата создания |
| `updated_at` | TIMESTAMP | Дата обновления |

### Metadata (примеры)

```json
{
  "gemini_model": "gemini-pro-vision",
  "generation_time": 5.3,
  "file_size": 245678,
  "colors": ["#FF5733", "#3498DB"],
  "style": "modern"
}
```

### Связи

- **generations** (1:1) — одна генерация = одна инфографика

---

## 5. Таблица: balance_transactions

История транзакций с балансом (опционально для МВП).

### Структура

```sql
CREATE TABLE balance_transactions (
    id              BIGSERIAL PRIMARY KEY,
    user_id         BIGINT NOT NULL,
    generation_id   BIGINT NULL,
    amount          INTEGER NOT NULL,
    type            VARCHAR(50) NOT NULL,
    description     TEXT NULL,
    created_at      TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT fk_transactions_user
        FOREIGN KEY (user_id)
        REFERENCES users(id)
        ON DELETE CASCADE,

    CONSTRAINT fk_transactions_generation
        FOREIGN KEY (generation_id)
        REFERENCES generations(id)
        ON DELETE SET NULL,

    CONSTRAINT chk_transaction_type
        CHECK (type IN ('generation', 'refund', 'manual_adjustment', 'purchase'))
);

CREATE INDEX idx_transactions_user_id ON balance_transactions(user_id);
CREATE INDEX idx_transactions_created_at ON balance_transactions(created_at DESC);
CREATE INDEX idx_transactions_type ON balance_transactions(type);
```

### Поля

| Поле | Тип | Описание |
|------|-----|----------|
| `id` | BIGSERIAL | Уникальный идентификатор |
| `user_id` | BIGINT | ID пользователя (FK) |
| `generation_id` | BIGINT | ID генерации (FK, опционально) |
| `amount` | INTEGER | Сумма (может быть отрицательной) |
| `type` | VARCHAR(50) | Тип транзакции |
| `description` | TEXT | Описание транзакции |
| `created_at` | TIMESTAMP | Дата создания |

### Типы транзакций

- `generation` — списание за генерацию (amount < 0)
- `refund` — возврат средств (amount > 0)
- `manual_adjustment` — ручная корректировка админом (amount любое)
- `purchase` — пополнение баланса (amount > 0, после МВП)

### Пример записей

```sql
-- Списание за генерацию
INSERT INTO balance_transactions (user_id, generation_id, amount, type, description)
VALUES (1, 42, -10, 'generation', 'Генерация инфографики');

-- Возврат при ошибке
INSERT INTO balance_transactions (user_id, generation_id, amount, type, description)
VALUES (1, 42, 10, 'refund', 'Возврат за ошибку генерации');

-- Пополнение админом
INSERT INTO balance_transactions (user_id, amount, type, description)
VALUES (1, 1000, 'manual_adjustment', 'Тестовые кредиты');
```

---

## 6. Таблица: password_reset_tokens

Токены для восстановления пароля (Laravel default).

### Структура

```sql
CREATE TABLE password_reset_tokens (
    email       VARCHAR(255) PRIMARY KEY,
    token       VARCHAR(255) NOT NULL,
    created_at  TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
);

CREATE INDEX idx_password_reset_tokens_email ON password_reset_tokens(email);
```

### Поля

| Поле | Тип | Описание |
|------|-----|----------|
| `email` | VARCHAR(255) | Email пользователя (PK) |
| `token` | VARCHAR(255) | Хэш токена восстановления |
| `created_at` | TIMESTAMP | Дата создания токена |

### Бизнес-правила

- Токен действителен 1 час
- При создании нового токена старый перезаписывается
- После сброса пароля токен удаляется

---

## 7. Диаграмма связей (ER Diagram)

```
┌─────────────┐
│    users    │
├─────────────┤
│ id          │◄─────┐
│ email       │      │
│ password    │      │
└─────────────┘      │
       ▲             │
       │             │
       │ 1:1         │ N:1
       │             │
┌─────────────┐      │
│  balances   │      │
├─────────────┤      │
│ id          │      │
│ user_id     │──────┘
│ credits     │
└─────────────┘

       ▲
       │
       │ N:1
       │
┌──────────────────┐
│   generations    │
├──────────────────┤
│ id               │
│ uuid             │
│ user_id          │──────┐
│ generatable_type │      │
│ generatable_id   │──┐   │
│ prompt           │  │   │
│ status           │  │   │
│ moderation_status│  │   │
│ file_path        │  │   │
└──────────────────┘  │   │
       ▲              │   │
       │              │   │
       │ 1:1          │   │ N:1
       │              │   │
┌──────────────────┐  │   │
│  infographics    │  │   │
├──────────────────┤  │   │
│ id               │  │   │
│ generation_id    │──┘   │
│ width            │      │
│ height           │      │
│ format           │      │
│ metadata         │      │
└──────────────────┘      │
                          │
                          │ N:1
                          │
┌──────────────────────┐  │
│ balance_transactions │  │
├──────────────────────┤  │
│ id                   │  │
│ user_id              │──┘
│ generation_id        │
│ amount               │
│ type                 │
└──────────────────────┘
```

---

## 8. Миграции Laravel

### Порядок выполнения

1. `create_users_table`
2. `create_balances_table`
3. `create_generations_table`
4. `create_infographics_table`
5. `create_balance_transactions_table`
6. `create_password_reset_tokens_table`

### Пример миграции: generations

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('generations', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('generatable_type');
            $table->unsignedBigInteger('generatable_id');
            $table->text('prompt');
            $table->enum('status', ['pending', 'processing', 'completed', 'failed'])
                  ->default('pending');
            $table->enum('moderation_status', ['pending', 'approved', 'rejected'])
                  ->default('approved');
            $table->integer('cost');
            $table->string('file_path', 500);
            $table->string('public_url', 500)->nullable();
            $table->text('error_message')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'created_at']);
            $table->index(['generatable_type', 'generatable_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('generations');
    }
};
```

---

## 9. Seeders (тестовые данные)

### UserSeeder

```php
// Создать 10 тестовых пользователей
User::factory(10)->create()->each(function ($user) {
    // Автоматически создается баланс через observer
    Balance::create([
        'user_id' => $user->id,
        'credits' => 1000,
    ]);
});
```

### GenerationSeeder

```php
// Создать 50 тестовых генераций
User::all()->each(function ($user) {
    Generation::factory(5)->for($user)->create()->each(function ($generation) {
        Infographic::factory()->for($generation)->create();
    });
});
```

---

## 10. Индексы и оптимизация

### Важные индексы

**Для производительности:**
- `generations.user_id` — выборка истории пользователя
- `generations.uuid` — поиск по публичной ссылке
- `generations.created_at DESC` — сортировка от новых к старым
- `generations (generatable_type, generatable_id)` — полиморфные связи

**Для уникальности:**
- `users.email` — один email = один аккаунт
- `balances.user_id` — один баланс = один пользователь
- `generations.uuid` — уникальные публичные ссылки

### Партиционирование (после МВП)

При большом объеме данных:
- Партиционирование `generations` по дате (месяц/год)
- Партиционирование `balance_transactions` по дате

```sql
-- Пример партиционирования по месяцам
CREATE TABLE generations_2025_01 PARTITION OF generations
    FOR VALUES FROM ('2025-01-01') TO ('2025-02-01');
```

---

## 11. Backup Strategy

### Ежедневный бэкап

```bash
# Full backup
pg_dump -U postgres -d generator > backup_$(date +%Y%m%d).sql

# Только данные (без структуры)
pg_dump -U postgres -d generator --data-only > data_$(date +%Y%m%d).sql
```

### Инкрементальный бэкап

- Использовать WAL архивирование PostgreSQL
- Point-in-time recovery (PITR)

---

## 12. Соглашения об именовании

### Таблицы
- Множественное число: `users`, `generations`, `infographics`
- Snake_case: `balance_transactions`

### Колонки
- Snake_case: `user_id`, `created_at`, `moderation_status`
- Foreign keys: `{table}_id` (например, `user_id`)

### Индексы
- `idx_{table}_{column}` (например, `idx_users_email`)
- `idx_{table}_{column1}_{column2}` для составных

### Constraints
- `fk_{table}_{referenced_table}` для foreign keys
- `chk_{description}` для check constraints
