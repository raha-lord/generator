# Интеграция Pollinations.ai для генерации изображений

## Обзор

Добавлена интеграция с **Pollinations.ai** - бесплатным API для генерации AI изображений без необходимости регистрации и получения токена.

## Основные особенности

### Преимущества Pollinations.ai
- ✅ Полностью бесплатный
- ✅ Не требует API ключа
- ✅ Поддержка различных моделей (Flux, Flux Realism, Turbo)
- ✅ Гибкие настройки размеров изображений
- ✅ Опция улучшения качества

### Реализованный функционал
- Генерация изображений по текстовому промпту
- Выбор модели AI (flux, flux-realism, turbo)
- Настройка размеров (512px - 2048px)
- Опция улучшения качества
- Стоимость: 5 кредитов за генерацию (дешевле инфографики)
- Интеграция с системой баланса и историей

## Структура файлов

### Backend

**Модели:**
- `app/Models/Image.php` - модель изображения с полиморфной связью

**Сервисы:**
- `app/Services/AI/PollinationsService.php` - базовый сервис для Pollinations.ai API
- `app/Services/AI/Providers/ImageGenerator.php` - провайдер с реализацией AIServiceInterface
- `app/Services/AI/AIServiceFactory.php` - обновлён для поддержки типа 'image'
- `app/Services/StorageService.php` - добавлен метод storeImage()

**Контроллеры:**
- `app/Http/Controllers/ImageController.php` - CRUD операции для изображений
- `app/Http/Requests/GenerateImageRequest.php` - валидация запросов

**Миграции:**
- `database/migrations/2025_10_21_000001_create_images_table.php`

### Frontend

**Views:**
- `resources/views/image/create.blade.php` - форма создания изображения
- `resources/views/image/show.blade.php` - просмотр сгенерированного изображения
- `resources/views/dashboard.blade.php` - добавлена карточка генерации изображений
- `resources/views/history/index.blade.php` - добавлен фильтр по типу "AI Image"
- `resources/views/layouts/navigation.blade.php` - добавлена ссылка "AI Image"

### Конфигурация

**config/services.php:**
```php
'pollinations' => [
    'base_url' => env('POLLINATIONS_BASE_URL', 'https://image.pollinations.ai/prompt'),
    'default_model' => env('POLLINATIONS_DEFAULT_MODEL', 'flux'),
    'default_width' => env('POLLINATIONS_DEFAULT_WIDTH', 1024),
    'default_height' => env('POLLINATIONS_DEFAULT_HEIGHT', 1024),
],
```

**routes/web.php:**
```php
Route::prefix('image')->name('image.')->group(function () {
    Route::get('/create', [ImageController::class, 'create'])->name('create');
    Route::post('/', [ImageController::class, 'store'])->name('store');
    Route::get('/{uuid}', [ImageController::class, 'show'])->name('show');
});
```

## Схема базы данных

### Таблица `images`

| Поле | Тип | Описание |
|------|-----|----------|
| id | bigint | Primary key |
| image_path | string | Путь к файлу изображения |
| thumbnail_path | string | Путь к миниатюре (nullable) |
| width | integer | Ширина изображения (default: 1024) |
| height | integer | Высота изображения (default: 1024) |
| format | string | Формат файла (default: 'png') |
| file_size | integer | Размер файла в байтах (nullable) |
| model | string | Используемая AI модель (default: 'flux') |
| seed | string | Seed для воспроизводимости (nullable) |
| enhanced | boolean | Был ли использован enhance (default: false) |
| created_at | timestamp | Дата создания |
| updated_at | timestamp | Дата обновления |

## Установка и настройка

### 1. Запуск миграции

```bash
# В Docker окружении
docker-compose exec app php artisan migrate

# Локально
php artisan migrate
```

### 2. Опциональные настройки .env

```env
# Pollinations.ai настройки (опционально, есть значения по умолчанию)
POLLINATIONS_BASE_URL=https://image.pollinations.ai/prompt
POLLINATIONS_DEFAULT_MODEL=flux
POLLINATIONS_DEFAULT_WIDTH=1024
POLLINATIONS_DEFAULT_HEIGHT=1024
```

## Использование

### Через веб-интерфейс

1. Войдите в систему
2. Перейдите в **Dashboard** или кликните **AI Image** в навигации
3. Заполните форму:
   - **Prompt**: описание изображения (10-1000 символов)
   - **Width/Height**: размеры изображения (512-2048px)
   - **Model**: выберите AI модель
   - **Enhance**: опция улучшения качества
4. Нажмите **Generate Image**
5. Просмотрите результат и скачайте изображение

### Программно

```php
use App\Services\AI\AIServiceFactory;
use App\Services\StorageService;
use App\Models\Image;
use App\Models\Generation;

// Получить сервис
$service = AIServiceFactory::make('image');

// Сгенерировать изображение
$result = $service->generate('A beautiful sunset over the ocean', [
    'width' => 1024,
    'height' => 1024,
    'model' => 'flux',
    'enhance' => true,
]);

if ($result['success']) {
    // Сохранить изображение
    $storageService = new StorageService();
    $storedFile = $storageService->storeImage(
        $result['data']['image_data'],
        $result['data']['format']
    );

    // Создать записи в БД
    $image = Image::create([
        'image_path' => $storedFile['path'],
        'width' => 1024,
        'height' => 1024,
        'model' => 'flux',
        'format' => 'png',
        // ...
    ]);

    $generation = Generation::create([
        'user_id' => auth()->id(),
        'generatable_type' => Image::class,
        'generatable_id' => $image->id,
        'prompt' => 'A beautiful sunset',
        'cost' => $service->getCost(),
        // ...
    ]);
}
```

## Доступные модели

| Модель | Описание | Рекомендовано для |
|--------|----------|-------------------|
| **flux** | Сбалансированная модель (по умолчанию) | Общее использование |
| **flux-realism** | Фотореалистичная модель | Реалистичные изображения |
| **turbo** | Быстрая генерация | Быстрые прототипы |

## Особенности реализации

### Архитектура

- **Factory Pattern**: `AIServiceFactory` управляет созданием сервисов
- **Полиморфизм**: `Generation` модель связана полиморфно с `Image` и `Infographic`
- **Service Layer**: бизнес-логика изолирована в сервисах
- **Request Validation**: валидация входных данных через FormRequest

### Обработка ошибок

- Автоматический возврат кредитов при неудачной генерации
- Логирование всех запросов и ошибок
- Пользовательские сообщения об ошибках

### Безопасность

- Rate limiting на маршруты генерации
- Валидация всех входных данных
- Проверка прав доступа к генерациям
- Защита от SQL injection через Eloquent ORM

## Мониторинг и логи

Все операции логируются в Laravel log:

```php
// Успешная генерация
Log::info('Image generation successful', [
    'mime_type' => $result['mime_type'],
    'format' => $result['format'],
    'model' => $result['metadata']['model'],
]);

// Ошибка генерации
Log::error('ImageGenerator error', [
    'message' => $e->getMessage(),
    'prompt' => substr($prompt, 0, 100),
]);
```

## Производительность

- **Среднее время генерации**: 5-15 секунд
- **Размер изображения**: ~200KB - 2MB в зависимости от размеров
- **Timeout**: 120 секунд на запрос к API

## Масштабирование

Для высоконагруженных систем рекомендуется:

1. Использовать очереди для асинхронной генерации
2. Настроить CDN для хранения изображений
3. Добавить кэширование частых запросов
4. Реализовать пакетную генерацию

## Будущие улучшения

- [ ] Асинхронная генерация через очереди
- [ ] Автоматическое создание миниатюр
- [ ] Поддержка редактирования существующих изображений
- [ ] Вариации изображения с тем же seed
- [ ] Экспорт в различные форматы
- [ ] Публичная галерея лучших генераций

## Поддержка

При возникновении проблем:

1. Проверьте логи: `storage/logs/laravel.log`
2. Убедитесь что миграции выполнены
3. Проверьте доступность Pollinations.ai API
4. Проверьте права доступа к директории storage

## Ссылки

- [Pollinations.ai](https://pollinations.ai) - официальный сайт
- [GitHub проекта](https://github.com/raha-lord/generator)
- [Документация Laravel](https://laravel.com/docs)
