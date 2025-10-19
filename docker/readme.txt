# Инструкция по настройке Docker-окружения для Laravel проекта

Эта инструкция поможет вам настроить Docker-окружение для вашего Laravel проекта с внешней базой данных PostgreSQL и проброшенным портом 89.

## Предварительные требования

- Docker и Docker Compose установлены на вашей системе
- Git (для клонирования репозитория, если необходимо)
- Внешняя БД PostgreSQL, к которой есть доступ

## Шаги настройки

1. **Клонировать репозиторий (если применимо):**
```bash
git clone <ваш-репозиторий>
cd <ваш-репозиторий>
```

2. **Создать структуру папок для Docker:**
```bash
mkdir -p docker/nginx/conf.d docker/php
```

3. **Скопировать файлы конфигурации:**

Скопируйте все предоставленные файлы в соответствующие директории:
- `docker/Dockerfile`
- `docker/docker-compose.yml`
- `docker/nginx/conf.d/nginx.conf`
- `docker/php/local.ini`

4. **Настроить переменные окружения:**

Если у вас ещё нет файла `.env`, скопируйте пример:
```bash
cp .env.example .env
```

И обновите настройки базы данных в файле `.env`:
```
DB_CONNECTION=pgsql
DB_HOST=<адрес-вашей-бд>
DB_PORT=5432
DB_DATABASE=<имя-вашей-бд>
DB_USERNAME=<пользователь-бд>
DB_PASSWORD=<пароль-бд>
```

5. **Собрать и запустить контейнеры:**
```bash
cd docker
docker-compose up -d
```

6. **Установить зависимости Laravel:**
```bash
docker-compose exec app composer install
```

7. **Сгенерировать ключ приложения:**
```bash
docker-compose exec app php artisan key:generate
```

8. **Выполнить миграции базы данных:**
```bash
docker-compose exec app php artisan migrate
```

9. **Заполнить базу данных (если необходимо):**
```bash
docker-compose exec app php artisan db:seed
```

## Доступ к приложению

- Веб-приложение: http://localhost:89

## Полезные команды

- Просмотр логов контейнеров:
```bash
docker-compose logs -f
```

- Доступ к PHP-контейнеру:
```bash
docker-compose exec app bash
```

- Остановка контейнеров:
```bash
docker-compose down
```

## Структура файлов

```
проект/
├── docker/
│   ├── Dockerfile              # Конфигурация PHP-контейнера
│   ├── docker-compose.yml      # Конфигурация Docker-сервисов
│   ├── nginx/
│   │   └── conf.d/
│   │       └── nginx.conf      # Конфигурация Nginx
│   └── php/
│       └── local.ini           # Конфигурация PHP
└── [остальные файлы проекта]
```

## Примечания для добавления в Git

Для добавления Docker-конфигурации в Git:

```bash
git add docker
git commit -m "Добавлена конфигурация Docker"
git push
```

## Примечания для продакшена

Для продакшен-окружения рекомендуется:
1. Использовать переменные окружения для чувствительной информации
2. Настроить SSL/TLS для безопасных соединений
3. Оптимизировать конфигурации PHP и Nginx для продакшена
