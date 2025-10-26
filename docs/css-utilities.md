# 🎨 Кастомные CSS утилиты

## Обзор

В проект добавлены кастомные CSS классы для упрощения работы с цветами в светлой и темной темах.

## 📦 Доступные утилиты

### 1. `.content-box`
Базовый контейнер с автоматической адаптацией цвета текста.

```html
<div class="content-box">
    <!-- Контент здесь -->
</div>
```

**Эквивалентно:**
```html
<div class="bg-gray-50 dark:bg-gray-900 text-gray-900 dark:text-gray-100">
```

---

### 2. `.prompt-box`
Специальный блок для отображения промптов с padding и скругленными углами.

```html
<div class="prompt-box">
    <p class="text-sm">{{ $generation->prompt }}</p>
</div>
```

**Эквивалентно:**
```html
<div class="bg-gray-50 dark:bg-gray-900 text-gray-900 dark:text-gray-100 p-4 rounded-lg">
```

**Использование:** Отображение промптов пользователей на страницах генераций.

---

### 3. `.info-box`
Информационный блок (желтый/предупреждающий стиль).

```html
<div class="info-box">
    <p class="text-sm">Your image is being generated...</p>
</div>
```

**Эквивалентно:**
```html
<div class="bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-800 text-yellow-800 dark:text-yellow-200 rounded-lg p-4">
```

**Использование:** Статус "processing", информационные сообщения.

---

### 4. `.error-box`
Блок ошибок (красный стиль).

```html
<div class="error-box">
    <p class="text-sm">Generation failed. Your credits have been refunded.</p>
</div>
```

**Эквивалентно:**
```html
<div class="bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 text-red-800 dark:text-red-200 rounded-lg p-4">
```

**Использование:** Статус "failed", сообщения об ошибках.

---

### 5. `.success-box`
Блок успеха (зеленый стиль).

```html
<div class="success-box">
    <p class="text-sm">Generation completed successfully!</p>
</div>
```

**Эквивалентно:**
```html
<div class="bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 text-green-800 dark:text-green-200 rounded-lg p-4">
```

**Использование:** Уведомления об успешном выполнении операций.

---

## 🎯 Преимущества

### До:
```html
<div class="bg-gray-50 dark:bg-gray-900 text-gray-900 dark:text-gray-100 p-4 rounded-lg">
    <p class="text-sm">Длинная строка классов...</p>
</div>
```

**Проблемы:**
- ❌ Длинные строки классов
- ❌ Легко забыть `dark:text-gray-100`
- ❌ Плохая читаемость
- ❌ Повторяющийся код

### После:
```html
<div class="prompt-box">
    <p class="text-sm">Чисто и понятно!</p>
</div>
```

**Преимущества:**
- ✅ Короткие, понятные имена
- ✅ Автоматическая адаптация для dark mode
- ✅ Отличная читаемость
- ✅ DRY (Don't Repeat Yourself)

---

## 📝 Где используется

### В проекте обновлены файлы:

1. **`resources/views/image/show.blade.php`**
   - `.prompt-box` для промпта
   - `.info-box` для processing статуса
   - `.error-box` для failed статуса

2. **`resources/views/infographic/show.blade.php`**
   - `.prompt-box` для промпта
   - `.info-box` для processing статуса
   - `.error-box` для failed статуса

3. **`resources/views/history/show/Image.blade.php`**
   - `.prompt-box` для промпта
   - `.info-box` для processing статуса
   - `.error-box` для failed статуса

4. **`resources/views/history/show/Infographic.blade.php`**
   - `.prompt-box` для промпта
   - `.info-box` для processing статуса
   - `.error-box` для failed статуса

---

## 🔧 Как добавить новые утилиты

Откройте `resources/css/app.css` и добавьте новый класс в секцию `@layer components`:

```css
@layer components {
    .my-custom-box {
        @apply bg-blue-50 dark:bg-blue-900 text-blue-900 dark:text-blue-100 p-4 rounded-lg;
    }
}
```

Затем перекомпилируйте CSS:

```bash
npm run dev
# или
npm run build
```

---

## 🎨 Цветовая палитра

| Класс | Светлая тема | Темная тема | Использование |
|-------|-------------|-------------|---------------|
| `.content-box` | Серый фон, темный текст | Темный фон, светлый текст | Общий контент |
| `.prompt-box` | Серый фон, темный текст | Темный фон, светлый текст | Промпты |
| `.info-box` | Желтый фон, темный текст | Прозрачный желтый, светлый текст | Информация |
| `.error-box` | Красный фон, темный текст | Прозрачный красный, светлый текст | Ошибки |
| `.success-box` | Зеленый фон, темный текст | Прозрачный зеленый, светлый текст | Успех |

---

## 📚 Примеры использования

### Пример 1: Простой контентный блок
```html
<div class="content-box p-4 rounded-lg">
    <h3>Заголовок</h3>
    <p>Контент автоматически адаптируется к теме!</p>
</div>
```

### Пример 2: Форма с ошибкой
```html
@if($errors->any())
    <div class="error-box mb-4">
        <p class="text-sm font-semibold">Ошибка валидации:</p>
        <ul class="text-sm mt-2 list-disc list-inside">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif
```

### Пример 3: Уведомление об успехе
```html
@if(session('success'))
    <div class="success-box mb-4">
        <p class="text-sm">{{ session('success') }}</p>
    </div>
@endif
```

---

## 🚀 Компиляция CSS

После изменения `app.css` обязательно перекомпилируйте стили:

### Development mode (с hot reload):
```bash
npm run dev
```

### Production build (минифицированный):
```bash
npm run build
```

---

## 💡 Best Practices

1. **Используйте утилиты везде, где возможно** - не дублируйте длинные цепочки классов
2. **Создавайте новые утилиты** для часто повторяющихся паттернов
3. **Сохраняйте консистентность** - используйте одни и те же классы для одинаковых элементов
4. **Не забывайте про семантику** - имена классов должны отражать назначение, а не внешний вид

---

## 🔗 Связанные файлы

- `resources/css/app.css` - Определение утилит
- `tailwind.config.js` - Конфигурация Tailwind
- `vite.config.js` - Конфигурация сборки
- `package.json` - NPM скрипты для компиляции
