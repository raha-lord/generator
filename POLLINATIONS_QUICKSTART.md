# 🎨 Быстрый старт: Генерация AI изображений

## ✨ Что добавлено

Новый сервис для генерации изображений через **Pollinations.ai**:
- 🆓 **Бесплатно** без API ключей
- 🎨 3 AI модели (Flux, Flux Realism, Turbo)
- 📐 Настраиваемые размеры (512-2048px)
- 💰 Всего **5 кредитов** за генерацию

## 🚀 Запуск

### 1. Обновите базу данных

```bash
# В Docker
docker-compose exec app php artisan migrate

# Локально
php artisan migrate
```

### 2. Создайте symbolic link для storage

```bash
# В Docker
docker-compose exec app php artisan storage:link

# Локально
php artisan storage:link
```

**⚠️ Это важно!** Без symlink изображения не будут отображаться в браузере.

### 3. Готово! 🎉

Откройте приложение и найдите новую кнопку **"Generate AI Image"** на Dashboard.

## 📍 Где найти

- **Dashboard**: карточка "Generate AI Image"
- **Навигация**: ссылка "AI Image"
- **История**: фильтр "AI Image" для просмотра всех сгенерированных изображений

## 🎯 Как использовать

1. Нажмите **"Generate AI Image"**
2. Введите описание изображения (на английском для лучших результатов)
3. Выберите параметры:
   - **Модель**: Flux / Flux Realism / Turbo
   - **Размер**: 512px - 2048px
   - **Enhance**: улучшить качество (медленнее)
4. Нажмите **"Generate Image"**
5. Скачайте результат!

## 💡 Примеры промптов

### Хорошо работают:
```
A futuristic cityscape at sunset with flying cars
Photorealistic portrait of a cat wearing a crown
Abstract digital art with vibrant colors and geometric shapes
Minimalist logo design for a tech startup
```

### Советы:
- Пишите на **английском** языке
- Будьте **конкретны** в описании
- Укажите **стиль** (realistic, cartoon, minimalist и т.д.)
- Добавьте **детали** (colors, lighting, composition)

## 🔧 Технические детали

### Файловая структура:
```
app/
├── Http/Controllers/ImageController.php
├── Models/Image.php
├── Services/AI/
│   ├── PollinationsService.php
│   └── Providers/ImageGenerator.php

resources/views/image/
├── create.blade.php
└── show.blade.php

database/migrations/
└── 2025_10_21_000001_create_images_table.php
```

### Маршруты:
```
GET  /image/create        - Форма генерации
POST /image              - Создание генерации
GET  /image/{uuid}       - Просмотр результата
```

### API Endpoint:
```
https://image.pollinations.ai/prompt/{prompt}?
  width=1024
  &height=1024
  &model=flux
  &enhance=false
  &nologo=true
```

## 📚 Подробная документация

Смотрите полную документацию: [`docs/pollinations-integration.md`](docs/pollinations-integration.md)

## 🐛 Возможные проблемы

**Ошибка генерации:**
- Проверьте интернет-соединение
- Попробуйте другой промт
- Уменьшите размер изображения

**Не видно изображения:**
- Проверьте права на папку `storage/app/public`
- Выполните `php artisan storage:link`

**Недостаточно кредитов:**
- Генерация стоит 5 кредитов
- При неудачной генерации кредиты возвращаются

## 🎉 Примеры использования

### Пример 1: Логотип
```
Промпт: "Minimalist tech startup logo, blue and white colors, modern design"
Модель: Flux
Размер: 1024x1024
```

### Пример 2: Иллюстрация
```
Промпт: "Fantasy landscape with mountains and magical castle, vibrant colors"
Модель: Flux Realism
Размер: 1536x1024
Enhance: ✓
```

### Пример 3: Быстрый концепт
```
Промпт: "Simple icon for mobile app, flat design"
Модель: Turbo
Размер: 512x512
```

---

**Приятной генерации! 🎨✨**
