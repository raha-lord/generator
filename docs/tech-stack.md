# Технологический стек проекта

## Backend

### Framework и язык
- **Framework:** Laravel 11.x
- **PHP:** 8.2+
- **Dependency Manager:** Composer

### База данных
- **СУБД:** PostgreSQL 15+
- **ORM:** Eloquent (Laravel)

### Кэширование и очереди
- **Cache:** Redis
- **Sessions:** Redis
- **Queue:** Redis (структура готова, не используется в МВП)

---

## Frontend

### Шаблонизация и стили
- **Templates:** Blade Templates (Laravel)
- **CSS Framework:** Tailwind CSS (или Bootstrap)

### JavaScript
- **Vanilla JavaScript** для динамического отображения
- **AJAX** для асинхронных запросов
- **Возможности:**
  - Отправка форм без перезагрузки
  - Индикаторы загрузки
  - Polling статусов генерации (опционально)
  - Копирование ссылок

---

## Infrastructure

### Контейнеризация
- **Docker** + Docker Compose
- **Основные сервисы:**
  - app (Laravel)
  - nginx (веб-сервер)
  - postgres (база данных)
  - redis (кэш и очереди)
  - minio (S3-compatible storage для dev)

### Storage
- **Local Storage** (для разработки)
- **S3-compatible** (MinIO для dev, AWS S3 для production)
- **Доступ:** через контроллеры (private storage)

---

## AI Integration

### Google Gemini API
- **Модель:** gemini-pro-vision
- **Endpoint:** `https://generativelanguage.googleapis.com/v1beta/models/gemini-pro-vision:generateContent`
- **Использование:** генерация инфографики
- **Конфигурация:** API ключ в `.env` (`GEMINI_API_KEY`)

---

## Development Tools

### Version Control
- **Git** для контроля версий
- **GitHub/GitLab** для хранения репозитория

### Local Development
- **Laravel Sail** (опционально)
- **Docker Compose** для локального окружения

### Testing
- **PHPUnit** для unit и feature тестов
- **Laravel Testing** (встроенные возможности)

### Code Quality
- **PHP CS Fixer** или **Laravel Pint** для форматирования
- **PHPStan** или **Larastan** для статического анализа (опционально)

---

## Security

### Встроенные механизмы Laravel
- **CSRF Protection** (по умолчанию)
- **Password Hashing:** bcrypt
- **SQL Injection Protection:** Eloquent ORM
- **XSS Protection:** Blade автоэкранирование

### Дополнительные меры
- **Rate Limiting** на генерацию (throttle middleware)
- **Environment Variables** для чувствительных данных
- **Private Storage** для пользовательских файлов

---

## Environment Variables

### Основные переменные окружения
```env
# Application
APP_NAME="AI Content Generator"
APP_ENV=local
APP_KEY=
APP_DEBUG=true
APP_URL=http://localhost

# Database
DB_CONNECTION=pgsql
DB_HOST=postgres
DB_PORT=5432
DB_DATABASE=generator
DB_USERNAME=postgres
DB_PASSWORD=secret

# Redis
REDIS_HOST=redis
REDIS_PASSWORD=null
REDIS_PORT=6379

# AI Services
GEMINI_API_KEY=your_api_key_here

# Storage (S3)
AWS_ACCESS_KEY_ID=
AWS_SECRET_ACCESS_KEY=
AWS_DEFAULT_REGION=us-east-1
AWS_BUCKET=
AWS_USE_PATH_STYLE_ENDPOINT=false

# Mail (для восстановления пароля)
MAIL_MAILER=smtp
MAIL_HOST=mailhog
MAIL_PORT=1025
MAIL_USERNAME=null
MAIL_PASSWORD=null
MAIL_ENCRYPTION=null
MAIL_FROM_ADDRESS="noreply@generator.local"
MAIL_FROM_NAME="${APP_NAME}"
```

---

## Production Requirements

### Минимальные требования сервера
- **PHP:** 8.2+
- **PostgreSQL:** 15+
- **Redis:** 6+
- **Web Server:** Nginx или Apache
- **Disk Space:** минимум 10GB (для хранения генераций)
- **RAM:** минимум 2GB

### Рекомендуемые настройки PHP
```ini
memory_limit = 256M
upload_max_filesize = 10M
post_max_size = 10M
max_execution_time = 120
```

---

## Масштабирование (после МВП)

### Горизонтальное масштабирование
- Load Balancer (nginx/HAProxy)
- Несколько экземпляров Laravel
- Shared Redis для сессий

### Очереди
- Laravel Horizon для мониторинга
- Supervisor для управления workers
- Отдельные worker сервера

### Storage
- CDN для статики (CloudFlare, CloudFront)
- Separate S3 bucket для пользовательского контента

### Мониторинг
- Laravel Telescope (development)
- Sentry для логирования ошибок
- New Relic/DataDog для метрик (опционально)
