# 🔧 Исправление проблемы с отображением изображений

## Проблема
Сгенерированные изображения не отображаются в браузере.

## Причины и решения

### 1. ✅ Исправлено: Неправильные URL в шаблонах

**Проблема:** Использовался `Storage::disk('public')->url()` вместо аксессоров модели.

**Решение:** Обновлены шаблоны для использования `$generation->generatable->image_url`

### 2. 🔧 Необходимо: Создать symbolic link

Laravel хранит файлы в `storage/app/public`, но они должны быть доступны через `public/storage`.

**Выполните команду:**

```bash
# В Docker
docker-compose exec app php artisan storage:link

# Локально
php artisan storage:link
```

**Ожидаемый результат:**
```
The [public/storage] link has been connected to [storage/app/public].
```

### 3. 🔧 Проверить права доступа

Убедитесь, что у папки storage есть права на запись:

```bash
# В Docker
docker-compose exec app chmod -R 775 storage
docker-compose exec app chmod -R 775 bootstrap/cache

# Локально
chmod -R 775 storage
chmod -R 775 bootstrap/cache
```

### 4. 🔧 Проверить структуру папок

Убедитесь, что существуют следующие директории:

```bash
storage/
├── app/
│   ├── public/
│   │   ├── images/        # Для AI изображений
│   │   └── infographics/  # Для инфографики
│   └── private/

public/
└── storage/  # Symlink на storage/app/public
```

**Создать папки вручную (если нужно):**

```bash
# В Docker
docker-compose exec app mkdir -p storage/app/public/images
docker-compose exec app mkdir -p storage/app/public/infographics

# Локально
mkdir -p storage/app/public/images
mkdir -p storage/app/public/infographics
```

## Быстрая проверка

### Шаг 1: Создать symlink

```bash
docker-compose exec app php artisan storage:link
```

### Шаг 2: Проверить права

```bash
docker-compose exec app ls -la public/ | grep storage
docker-compose exec app ls -la storage/app/public/
```

Вы должны увидеть:
- `public/storage` -> `../storage/app/public` (symlink)
- Папки `images/` и `infographics/` в `storage/app/public/`

### Шаг 3: Сгенерировать тестовое изображение

1. Перейдите на `/image/create`
2. Введите промпт: `"A beautiful sunset over the ocean"`
3. Нажмите "Generate Image"
4. Проверьте результат

## Отладка

### Проверить, где сохраняется файл

Добавьте временно в `ImageController.php` после строки 112:

```php
Log::info('Image stored', [
    'path' => $storedFile['path'],
    'full_path' => storage_path('app/public/' . $storedFile['path']),
    'exists' => file_exists(storage_path('app/public/' . $storedFile['path'])),
]);
```

Проверьте логи:
```bash
docker-compose exec app tail -f storage/logs/laravel.log
```

### Проверить URL изображения

В браузере откройте Developer Tools (F12) и проверьте:
1. Вкладка Network -> найдите запрос к изображению
2. Проверьте статус код (должен быть 200)
3. Проверьте путь к изображению

**Правильный путь должен быть:**
```
http://localhost:8089/storage/images/image_2025-10-21_123456_abc12345.png
```

### Если изображение всё ещё не отображается

1. **Очистить кэш:**
   ```bash
   docker-compose exec app php artisan cache:clear
   docker-compose exec app php artisan config:clear
   docker-compose exec app php artisan view:clear
   ```

2. **Проверить .env файл:**
   ```env
   APP_URL=http://localhost:8089
   FILESYSTEM_DISK=public
   ```

3. **Перезапустить контейнеры:**
   ```bash
   docker-compose restart
   ```

## Итоговый чек-лист

- [ ] Выполнена команда `php artisan storage:link`
- [ ] Symlink `public/storage` создан
- [ ] Папка `storage/app/public/images` существует
- [ ] Права доступа установлены (775)
- [ ] Шаблоны используют `$generation->generatable->image_url`
- [ ] Тестовое изображение успешно отображается

## Если ничего не помогло

Создайте issue на GitHub с информацией:
1. Вывод команды `ls -la public/ | grep storage`
2. Вывод команды `ls -la storage/app/public/`
3. Скриншот ошибки из DevTools (вкладка Network)
4. Содержимое `storage/logs/laravel.log` (последние 50 строк)

---

**Примечание:** После исправления обязательно закоммитьте изменения!
