# Техническое задание: AI Content Generator (МВП)

## Общее описание проекта

Веб-платформа для генерации различных типов контента с использованием AI. МВП включает базовую инфраструктуру и первый функционал — генерацию инфографики через Gemini API.

### Ключевые принципы:
- Модульная расширяемая архитектура для добавления новых AI-сервисов
- Масштабируемость под рост нагрузки
- Простота добавления новых типов генерации контента

---

## Структура документации

Для удобства работы техническое задание разделено на несколько документов:

### 📋 Основные документы

1. **[Технологический стек](./tech-stack.md)**
   - Backend (Laravel, PostgreSQL, Redis)
   - Frontend (Blade, JavaScript, Tailwind CSS)
   - Infrastructure (Docker, Storage, AI API)
   - Development Tools

2. **[Бизнес-логика](./business-logic.md)**
   - Аутентификация и пользователи
   - Система балансов
   - Генерация инфографики
   - История и публичные ссылки
   - Модерация
   - Архитектура AI-сервисов

3. **[Структура базы данных](./database-structure.md)**
   - Таблицы и связи
   - Миграции Laravel
   - Индексы и оптимизация
   - Seeders для тестовых данных

4. **[План разработки МВП](./mvp-development-plan.md)**
   - 10 этапов разработки
   - Сроки (19-30 дней)
   - Критерии приемки
   - Метрики успеха

5. **[Стандарты кодирования](./code-style-standards.md)** ⚠️ **ОБЯЗАТЕЛЬНО**
   - PSR-12 для PHP, ES6+ для JavaScript
   - Инструменты проверки кода (PHP CS Fixer, PHPStan, ESLint)
   - Git hooks и CI/CD настройка
   - Примеры правильного кода

---

## Краткое описание функционала МВП

### Аутентификация
- Регистрация (email + пароль)
- Авторизация ("Запомнить меня")
- Восстановление пароля

### Система балансов
- 1000 кредитов при регистрации
- Списание за генерацию (10 кредитов)
- Без пополнения в МВП (добавляется позже)

### Генерация инфографики
- Ввод текстового промта (до 1000 символов)
- Генерация через Gemini API
- Отображение результата
- Сохранение в историю

### История генераций
- Список всех генераций пользователя
- Просмотр в полном размере
- Скачивание
- Публичные ссылки для sharing

### Личный кабинет
- Просмотр баланса
- Изменение пароля
- История транзакций

---

## API Endpoints

### Аутентификация
```
POST   /register               - Регистрация
POST   /login                  - Вход
POST   /logout                 - Выход
POST   /forgot-password        - Запрос восстановления
POST   /reset-password         - Сброс пароля
```

### Профиль
```
GET    /profile                - Личный кабинет
PUT    /profile/password       - Изменение пароля
GET    /profile/balance        - Текущий баланс
```

### Генерация инфографики
```
GET    /infographic/create     - Страница генерации
POST   /infographic/generate   - Запуск генерации
GET    /infographic/{id}       - Просмотр результата
```

### История
```
GET    /history                - Список всех генераций
GET    /history/{id}           - Детали генерации
DELETE /history/{id}           - Удаление из истории
```

### Публичные ссылки
```
GET    /share/{uuid}           - Публичный просмотр
```

---

## Архитектура проекта

### Структура Laravel

```
app/
├── Models/
│   ├── User.php
│   ├── Generation.php (полиморфная)
│   ├── Infographic.php
│   └── Balance.php
├── Services/
│   ├── AI/
│   │   ├── AIServiceInterface.php
│   │   ├── AIServiceFactory.php
│   │   ├── GeminiService.php
│   │   └── Providers/
│   │       ├── InfographicGenerator.php
│   │       ├── TextGenerator.php (заглушка)
│   │       └── DescriptionGenerator.php (заглушка)
│   ├── BalanceService.php
│   └── StorageService.php
├── Http/
│   ├── Controllers/
│   │   ├── Auth/
│   │   ├── ProfileController.php
│   │   ├── InfographicController.php
│   │   └── GenerationHistoryController.php
│   ├── Requests/
│   │   └── GenerateInfographicRequest.php
│   └── Middleware/
│       └── CheckBalance.php
├── Jobs/ (для будущего)
│   └── ProcessGenerationJob.php
└── Exceptions/
    ├── InsufficientBalanceException.php
    └── AIServiceException.php
```

### Паттерн Factory для AI-сервисов

**Интерфейс:**
```php
interface AIServiceInterface
{
    public function generate(string $prompt, array $options = []): mixed;
    public function getServiceType(): string;
    public function getCost(): int;
}
```

**Factory:**
```php
class AIServiceFactory
{
    public static function make(string $type): AIServiceInterface
    {
        return match($type) {
            'infographic' => new InfographicGenerator(),
            'text' => new TextGenerator(),
            'description' => new DescriptionGenerator(),
            default => throw new InvalidArgumentException()
        };
    }
}
```

### Workflow генерации

```
User Input (prompt)
    ↓
Controller: InfographicController@generate
    ↓
Validation (Request)
    ↓
Check Balance (Middleware/Service)
    ↓
AIServiceFactory::make('infographic')
    ↓
GeminiService->generate()
    ↓
Save to Storage (StorageService)
    ↓
Save to DB (Generation + Infographic)
    ↓
Deduct Balance
    ↓
Return Response (JSON/View)
```

---

## Метрики успеха МВП

- ✅ Пользователь может зарегистрироваться
- ✅ Пользователь может сгенерировать инфографику
- ✅ Баланс корректно списывается
- ✅ История сохраняется
- ✅ Публичные ссылки работают
- ✅ Нет критических багов
- ✅ Время генерации < 60 секунд
- ✅ UI интуитивно понятен

---

## Что НЕ входит в МВП

- ❌ Система очередей (Jobs + Horizon)
- ❌ Реальная оплата/пополнение баланса
- ❌ Модерация контента (только структура)
- ❌ Дополнительные AI-сервисы (только заглушки)
- ❌ Административная панель (кроме базовой)
- ❌ Email notifications (кроме восстановления пароля)
- ❌ Социальные сети для входа
- ❌ API для внешних клиентов
- ❌ Мобильное приложение
- ❌ Расширенная аналитика

---

## Дальнейшее развитие (после МВП)

### Фаза 2:
- Очереди для генерации
- Реальная платежная система (Stripe/PayPal)
- Модерация с использованием AI
- Email уведомления

### Фаза 3:
- Генерация текста (второй AI-сервис)
- Генерация описаний (третий AI-сервис)
- Улучшенная история (фильтры, поиск)

### Фаза 4:
- Публичный API
- Webhook система
- Административная панель (расширенная)
- Аналитика и метрики

---

## Безопасность

### Общие меры
- CSRF protection (Laravel default)
- XSS protection (escape output)
- SQL injection (Eloquent ORM)
- Rate limiting на генерацию (10 запросов в минуту)

### Валидация
- Все пользовательские входы валидируются
- Санитизация промтов перед отправкой в API
- Проверка типов файлов при загрузке

### Хранение данных
- Пароли: bcrypt (Laravel default)
- API ключи: в `.env`, не в коде
- Файлы: private storage, доступ через контроллер

---

## Deployment (Docker)

### Структура
```
docker-compose.yml
├── app (Laravel)
├── nginx
├── postgres
├── redis
└── minio (S3-compatible, для dev)
```

### Environment Variables
```env
APP_ENV=production
APP_KEY=...
DB_HOST=postgres
REDIS_HOST=redis
GEMINI_API_KEY=...
AWS_BUCKET=...
```

---

## Контакты и вопросы

Для уточнений и изменений ТЗ обращаться к Product Owner.

**Версия документа:** 2.0
**Дата создания:** 2025-10-19
**Последнее обновление:** 2025-10-19

---

## Ссылки на детальную документацию

- [Технологический стек →](./tech-stack.md)
- [Бизнес-логика →](./business-logic.md)
- [Структура БД →](./database-structure.md)
- [План разработки →](./mvp-development-plan.md)
- [**Стандарты кодирования →**](./code-style-standards.md) ⚠️ **Обязательно к применению**
