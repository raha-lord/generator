# Стандарты кодирования и Code Quality

## Общие принципы

### Философия кода
- **SOLID принципы** — обязательны для всех классов
- **DRY (Don't Repeat Yourself)** — избегать дублирования кода
- **KISS (Keep It Simple, Stupid)** — простота важнее сложности
- **YAGNI (You Aren't Gonna Need It)** — не добавлять функционал "на будущее"
- **Clean Code** — код пишется для людей, не для машин

### Именование
- **Понятные имена** — переменные, методы, классы должны объяснять свое назначение
- **Английский язык** — весь код только на английском
- **Не использовать транслит** — никаких `polzovatel`, `balans`
- **Избегать аббревиатур** — кроме общепринятых (HTML, API, URL)

---

## Backend (PHP/Laravel)

### PHP Standards Recommendations (PSR)

**Обязательные стандарты:**
- **PSR-1** — Basic Coding Standard
- **PSR-12** — Extended Coding Style Guide (заменяет PSR-2)
- **PSR-4** — Autoloading Standard

**Ключевые правила PSR-12:**
- Отступы: 4 пробела (не табы)
- Максимальная длина строки: 120 символов (soft limit)
- Открывающая фигурная скобка `{` класса/метода на новой строке
- Closing `}` на отдельной строке
- `declare(strict_types=1);` в начале каждого PHP файла

### Пример правильного кода

```php
<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\User;
use App\Exceptions\InsufficientBalanceException;

class BalanceService
{
    public function __construct(
        private readonly User $user
    ) {
    }

    public function deductCredits(int $amount, string $description): void
    {
        if (!$this->hasEnoughCredits($amount)) {
            throw new InsufficientBalanceException(
                "Insufficient credits. Required: {$amount}, Available: {$this->getBalance()}"
            );
        }

        $this->user->balance->decrement('credits', $amount);

        $this->createTransaction(
            amount: -$amount,
            type: 'generation',
            description: $description
        );
    }

    private function hasEnoughCredits(int $amount): bool
    {
        return $this->user->balance->credits >= $amount;
    }

    private function getBalance(): int
    {
        return $this->user->balance->credits;
    }

    private function createTransaction(
        int $amount,
        string $type,
        string $description
    ): void {
        $this->user->balanceTransactions()->create([
            'amount' => $amount,
            'type' => $type,
            'description' => $description,
        ]);
    }
}
```

### Laravel Code Style

#### Controllers
```php
<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\GenerateInfographicRequest;
use App\Services\AI\AIServiceFactory;
use App\Services\BalanceService;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;

class InfographicController extends Controller
{
    public function __construct(
        private readonly BalanceService $balanceService,
        private readonly AIServiceFactory $aiServiceFactory
    ) {
    }

    public function create(): View
    {
        return view('infographic.create', [
            'balance' => auth()->user()->balance->credits,
            'cost' => config('services.ai.infographic_cost'),
        ]);
    }

    public function store(GenerateInfographicRequest $request): JsonResponse
    {
        try {
            $user = auth()->user();

            // Check balance
            $this->balanceService->checkBalance($user, 10);

            // Generate infographic
            $generator = $this->aiServiceFactory->make('infographic');
            $result = $generator->generate($request->validated('prompt'));

            // Deduct credits
            $this->balanceService->deductCredits($user, 10, 'Infographic generation');

            return response()->json([
                'success' => true,
                'data' => $result,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }
}
```

**Правила для контроллеров:**
- Тонкие контроллеры (thin controllers) — бизнес-логика в сервисах
- Dependency Injection через конструктор
- Type hints для всех параметров и возвращаемых значений
- Использовать Form Requests для валидации
- Один метод = одна задача

#### Models
```php
<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Generation extends Model
{
    use HasFactory;

    protected $fillable = [
        'uuid',
        'user_id',
        'generatable_type',
        'generatable_id',
        'prompt',
        'status',
        'moderation_status',
        'cost',
        'file_path',
        'public_url',
        'error_message',
    ];

    protected $casts = [
        'cost' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    protected $attributes = [
        'status' => 'pending',
        'moderation_status' => 'approved',
    ];

    // Relationships
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function generatable(): MorphTo
    {
        return $this->morphTo();
    }

    // Scopes
    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    public function scopeRecent($query)
    {
        return $query->orderBy('created_at', 'desc');
    }

    // Accessors
    public function getPublicShareUrlAttribute(): string
    {
        return route('share.show', $this->uuid);
    }

    // Mutators
    protected function setPromptAttribute(string $value): void
    {
        $this->attributes['prompt'] = strip_tags($value);
    }
}
```

**Правила для моделей:**
- Явное определение `$fillable` или `$guarded`
- `$casts` для типизации атрибутов
- Type hints для relationships
- Scopes для частых запросов
- Accessors/Mutators для работы с атрибутами

#### Services
```php
<?php

declare(strict_types=1);

namespace App\Services\AI;

interface AIServiceInterface
{
    /**
     * Generate content based on prompt
     *
     * @param string $prompt User's text prompt
     * @param array<string, mixed> $options Additional options
     * @return mixed Generated content
     * @throws \App\Exceptions\AIServiceException
     */
    public function generate(string $prompt, array $options = []): mixed;

    /**
     * Get service type identifier
     *
     * @return string Service type (e.g., 'infographic', 'text')
     */
    public function getServiceType(): string;

    /**
     * Get generation cost in credits
     *
     * @return int Cost in credits
     */
    public function getCost(): int;
}
```

**Правила для сервисов:**
- Один сервис = одна зона ответственности
- Интерфейсы для всех сервисов
- PHPDoc блоки для всех public методов
- Type hints обязательны
- Внедрение зависимостей через конструктор

---

## Frontend (JavaScript)

### JavaScript Standards

**Стандарт:** ES6+ (ESNext)

**Ключевые правила:**
- `const` по умолчанию, `let` только когда нужна переменность
- Никогда не использовать `var`
- Arrow functions где возможно
- Template literals вместо конкатенации строк
- Async/await вместо Promise chains
- Деструктуризация где уместно

### Пример правильного кода

```javascript
// resources/js/infographic-generator.js

class InfographicGenerator {
    constructor(formSelector, resultSelector) {
        this.form = document.querySelector(formSelector);
        this.resultContainer = document.querySelector(resultSelector);
        this.submitButton = this.form.querySelector('button[type="submit"]');

        this.init();
    }

    init() {
        this.form.addEventListener('submit', (e) => this.handleSubmit(e));
        this.setupCharacterCounter();
    }

    setupCharacterCounter() {
        const textarea = this.form.querySelector('textarea[name="prompt"]');
        const counter = this.form.querySelector('.character-counter');
        const maxLength = 1000;

        textarea.addEventListener('input', (e) => {
            const currentLength = e.target.value.length;
            counter.textContent = `${currentLength} / ${maxLength}`;

            if (currentLength > maxLength) {
                counter.classList.add('text-red-500');
            } else {
                counter.classList.remove('text-red-500');
            }
        });
    }

    async handleSubmit(event) {
        event.preventDefault();

        const formData = new FormData(this.form);
        const prompt = formData.get('prompt');

        if (!this.validatePrompt(prompt)) {
            this.showError('Please enter a valid prompt');
            return;
        }

        this.setLoading(true);

        try {
            const result = await this.generateInfographic(prompt);
            this.displayResult(result);
            this.updateBalance(result.balance);
        } catch (error) {
            this.showError(error.message);
        } finally {
            this.setLoading(false);
        }
    }

    validatePrompt(prompt) {
        return prompt && prompt.trim().length > 0 && prompt.length <= 1000;
    }

    async generateInfographic(prompt) {
        const response = await fetch('/infographic/generate', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            },
            body: JSON.stringify({ prompt }),
        });

        if (!response.ok) {
            const error = await response.json();
            throw new Error(error.message || 'Generation failed');
        }

        return response.json();
    }

    displayResult(result) {
        const html = `
            <div class="result-card">
                <img src="${result.data.file_url}" alt="Generated infographic" />
                <div class="actions">
                    <button onclick="downloadImage('${result.data.file_url}')">Download</button>
                    <button onclick="copyShareLink('${result.data.share_url}')">Share</button>
                </div>
            </div>
        `;

        this.resultContainer.innerHTML = html;
        this.resultContainer.scrollIntoView({ behavior: 'smooth' });
    }

    updateBalance(newBalance) {
        const balanceElement = document.querySelector('.user-balance');
        if (balanceElement) {
            balanceElement.textContent = `${newBalance} credits`;
        }
    }

    showError(message) {
        // Use toast library or custom notification
        alert(message); // Replace with better UI
    }

    setLoading(isLoading) {
        this.submitButton.disabled = isLoading;
        this.submitButton.textContent = isLoading ? 'Generating...' : 'Generate';

        if (isLoading) {
            this.submitButton.classList.add('loading');
        } else {
            this.submitButton.classList.remove('loading');
        }
    }
}

// Initialize when DOM is ready
document.addEventListener('DOMContentLoaded', () => {
    new InfographicGenerator('#infographic-form', '#result-container');
});
```

**Правила JavaScript:**
- Классы для организации кода
- Один класс/модуль = один файл
- Async/await для асинхронных операций
- Try-catch для обработки ошибок
- Константы в UPPER_CASE
- Методы в camelCase
- Классы в PascalCase

### Blade Templates

```blade
{{-- resources/views/infographic/create.blade.php --}}

@extends('layouts.app')

@section('title', 'Create Infographic')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="max-w-2xl mx-auto">
        <h1 class="text-3xl font-bold mb-6">
            Generate Infographic
        </h1>

        <div class="bg-white rounded-lg shadow-md p-6">
            {{-- Balance Display --}}
            <div class="mb-4 flex justify-between items-center">
                <span class="text-gray-600">Your Balance:</span>
                <span class="user-balance font-bold text-lg">
                    {{ $balance }} credits
                </span>
            </div>

            <div class="mb-4 text-sm text-gray-500">
                Cost per generation: <strong>{{ $cost }} credits</strong>
            </div>

            {{-- Form --}}
            <form id="infographic-form" method="POST" action="{{ route('infographic.generate') }}">
                @csrf

                <div class="mb-4">
                    <label for="prompt" class="block text-gray-700 font-medium mb-2">
                        Describe your infographic
                    </label>

                    <textarea
                        id="prompt"
                        name="prompt"
                        rows="6"
                        maxlength="1000"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                        placeholder="Enter a detailed description of the infographic you want to create..."
                        required
                    >{{ old('prompt') }}</textarea>

                    <div class="flex justify-between items-center mt-2">
                        <span class="character-counter text-sm text-gray-500">
                            0 / 1000
                        </span>

                        @error('prompt')
                            <span class="text-red-500 text-sm">{{ $message }}</span>
                        @enderror
                    </div>
                </div>

                <button
                    type="submit"
                    class="w-full bg-blue-600 text-white font-medium py-3 px-6 rounded-lg hover:bg-blue-700 transition-colors disabled:bg-gray-400 disabled:cursor-not-allowed"
                >
                    Generate Infographic
                </button>
            </form>
        </div>

        {{-- Result Container --}}
        <div id="result-container" class="mt-8"></div>
    </div>
</div>
@endsection

@push('scripts')
    <script src="{{ asset('js/infographic-generator.js') }}" defer></script>
@endpush
```

**Правила Blade:**
- Комментарии: `{{-- comment --}}`
- Экранирование: `{{ $variable }}` (не `{!! $variable !!}` без причины)
- Директивы на отдельных строках
- Отступы для вложенности
- Секции: `@section`, `@yield`, `@push`, `@stack`

---

## CSS/Tailwind

### Tailwind CSS Guidelines

**Принципы:**
- Utility-first подход
- Не создавать кастомные CSS классы без необходимости
- Использовать `@apply` только для компонентов
- Responsive префиксы: `sm:`, `md:`, `lg:`, `xl:`
- Dark mode: `dark:` (если реализуется)

### Пример правильного использования

```html
<!-- Good: Tailwind utilities -->
<div class="container mx-auto px-4 py-8">
    <div class="max-w-2xl mx-auto bg-white rounded-lg shadow-md p-6">
        <h1 class="text-3xl font-bold text-gray-900 mb-4">
            Title
        </h1>
        <p class="text-gray-600 leading-relaxed">
            Description text
        </p>
    </div>
</div>

<!-- Bad: Inline styles -->
<div style="margin: 0 auto; padding: 2rem;">
    <h1 style="font-size: 1.875rem; font-weight: bold;">
        Title
    </h1>
</div>
```

**Кастомные компоненты (app.css):**
```css
/* resources/css/app.css */

@tailwind base;
@tailwind components;
@tailwind utilities;

/* Custom components using @apply */
@layer components {
    .btn-primary {
        @apply bg-blue-600 text-white font-medium py-2 px-4 rounded-lg;
        @apply hover:bg-blue-700 transition-colors;
        @apply disabled:bg-gray-400 disabled:cursor-not-allowed;
    }

    .card {
        @apply bg-white rounded-lg shadow-md p-6;
    }

    .form-input {
        @apply w-full px-4 py-2 border border-gray-300 rounded-lg;
        @apply focus:ring-2 focus:ring-blue-500 focus:border-transparent;
    }
}

/* Custom utilities */
@layer utilities {
    .text-balance {
        text-wrap: balance;
    }
}
```

---

## Инструменты для проверки кода

### 1. PHP CS Fixer

**Установка:**
```bash
composer require --dev friendsofphp/php-cs-fixer
```

**Конфигурация:** `.php-cs-fixer.php`
```php
<?php

$finder = PhpCsFixer\Finder::create()
    ->in(__DIR__)
    ->exclude('bootstrap/cache')
    ->exclude('storage')
    ->exclude('vendor')
    ->name('*.php')
    ->notName('*.blade.php')
    ->ignoreDotFiles(true)
    ->ignoreVCS(true);

return (new PhpCsFixer\Config())
    ->setRules([
        '@PSR12' => true,
        'array_syntax' => ['syntax' => 'short'],
        'ordered_imports' => ['sort_algorithm' => 'alpha'],
        'no_unused_imports' => true,
        'not_operator_with_successor_space' => true,
        'trailing_comma_in_multiline' => true,
        'phpdoc_scalar' => true,
        'unary_operator_spaces' => true,
        'binary_operator_spaces' => true,
        'blank_line_before_statement' => [
            'statements' => ['break', 'continue', 'declare', 'return', 'throw', 'try'],
        ],
        'phpdoc_single_line_var_spacing' => true,
        'phpdoc_var_without_name' => true,
        'method_argument_space' => [
            'on_multiline' => 'ensure_fully_multiline',
        ],
        'declare_strict_types' => true,
    ])
    ->setFinder($finder);
```

**Запуск:**
```bash
# Проверка
vendor/bin/php-cs-fixer fix --dry-run --diff

# Исправление
vendor/bin/php-cs-fixer fix
```

### 2. Laravel Pint (альтернатива PHP CS Fixer)

**Установка:**
```bash
composer require laravel/pint --dev
```

**Конфигурация:** `pint.json`
```json
{
    "preset": "laravel",
    "rules": {
        "declare_strict_types": true,
        "ordered_imports": {
            "sort_algorithm": "alpha"
        },
        "no_unused_imports": true,
        "trailing_comma_in_multiline": true
    }
}
```

**Запуск:**
```bash
# Проверка
./vendor/bin/pint --test

# Исправление
./vendor/bin/pint
```

### 3. PHPStan (статический анализ)

**Установка:**
```bash
composer require --dev phpstan/phpstan
composer require --dev larastan/larastan
```

**Конфигурация:** `phpstan.neon`
```neon
includes:
    - vendor/larastan/larastan/extension.neon

parameters:
    paths:
        - app
        - config
        - database
        - routes

    level: 8

    ignoreErrors:
        - '#Unsafe usage of new static#'

    excludePaths:
        - vendor
        - storage
        - bootstrap/cache

    checkMissingIterableValueType: false
```

**Запуск:**
```bash
# Анализ
vendor/bin/phpstan analyse

# С прогресс-баром
vendor/bin/phpstan analyse --memory-limit=2G
```

### 4. ESLint (JavaScript)

**Установка:**
```bash
npm install --save-dev eslint @eslint/js
```

**Конфигурация:** `eslint.config.js`
```javascript
import js from '@eslint/js';

export default [
    js.configs.recommended,
    {
        languageOptions: {
            ecmaVersion: 2022,
            sourceType: 'module',
            globals: {
                window: 'readonly',
                document: 'readonly',
                console: 'readonly',
                fetch: 'readonly',
            },
        },
        rules: {
            'indent': ['error', 4],
            'quotes': ['error', 'single'],
            'semi': ['error', 'always'],
            'no-unused-vars': 'warn',
            'no-console': 'warn',
            'prefer-const': 'error',
            'no-var': 'error',
            'arrow-spacing': 'error',
            'object-curly-spacing': ['error', 'always'],
            'array-bracket-spacing': ['error', 'never'],
        },
    },
];
```

**Запуск:**
```bash
# Проверка
npm run lint

# Исправление
npm run lint:fix
```

**package.json:**
```json
{
    "scripts": {
        "lint": "eslint resources/js",
        "lint:fix": "eslint resources/js --fix"
    }
}
```

### 5. Prettier (форматирование)

**Установка:**
```bash
npm install --save-dev prettier
```

**Конфигурация:** `.prettierrc.json`
```json
{
    "semi": true,
    "singleQuote": true,
    "tabWidth": 4,
    "trailingComma": "es5",
    "printWidth": 100,
    "arrowParens": "always",
    "bracketSpacing": true
}
```

**Запуск:**
```bash
# Проверка
npx prettier --check resources/js resources/css

# Исправление
npx prettier --write resources/js resources/css
```

---

## Git Hooks (автоматическая проверка)

### Установка Husky

```bash
npm install --save-dev husky lint-staged
npx husky init
```

### Pre-commit hook

**`.husky/pre-commit`:**
```bash
#!/usr/bin/env sh
. "$(dirname -- "$0")/_/husky.sh"

# PHP CS Fixer
vendor/bin/pint --test

# PHPStan
vendor/bin/phpstan analyse --error-format=table

# ESLint
npm run lint

# Prettier
npx prettier --check resources/js resources/css
```

### Lint-staged (только измененные файлы)

**package.json:**
```json
{
    "lint-staged": {
        "*.php": [
            "vendor/bin/pint",
            "vendor/bin/phpstan analyse --error-format=table"
        ],
        "*.js": [
            "eslint --fix",
            "prettier --write"
        ],
        "*.css": [
            "prettier --write"
        ]
    }
}
```

---

## CI/CD Integration

### GitHub Actions

**`.github/workflows/code-quality.yml`:**
```yaml
name: Code Quality

on: [push, pull_request]

jobs:
  php-cs:
    runs-on: ubuntu-latest
    steps:
      - uses: actions/checkout@v3

      - name: Setup PHP
        uses: shivammathur/setup-php@v2
        with:
          php-version: 8.2

      - name: Install dependencies
        run: composer install --prefer-dist --no-progress

      - name: Run PHP CS Fixer
        run: vendor/bin/pint --test

  phpstan:
    runs-on: ubuntu-latest
    steps:
      - uses: actions/checkout@v3

      - name: Setup PHP
        uses: shivammathur/setup-php@v2
        with:
          php-version: 8.2

      - name: Install dependencies
        run: composer install --prefer-dist --no-progress

      - name: Run PHPStan
        run: vendor/bin/phpstan analyse

  eslint:
    runs-on: ubuntu-latest
    steps:
      - uses: actions/checkout@v3

      - name: Setup Node
        uses: actions/setup-node@v3
        with:
          node-version: 18

      - name: Install dependencies
        run: npm ci

      - name: Run ESLint
        run: npm run lint
```

---

## Composer Scripts

**composer.json:**
```json
{
    "scripts": {
        "test": "vendor/bin/phpunit",
        "test:coverage": "vendor/bin/phpunit --coverage-html coverage",
        "cs:check": "vendor/bin/pint --test",
        "cs:fix": "vendor/bin/pint",
        "stan": "vendor/bin/phpstan analyse --memory-limit=2G",
        "quality": [
            "@cs:check",
            "@stan",
            "@test"
        ]
    }
}
```

**Запуск:**
```bash
# Проверка кода
composer cs:check

# Исправление кода
composer cs:fix

# Статический анализ
composer stan

# Все проверки сразу
composer quality
```

---

## NPM Scripts

**package.json:**
```json
{
    "scripts": {
        "dev": "vite",
        "build": "vite build",
        "lint": "eslint resources/js",
        "lint:fix": "eslint resources/js --fix",
        "format": "prettier --write resources/js resources/css",
        "format:check": "prettier --check resources/js resources/css",
        "quality": "npm run lint && npm run format:check"
    }
}
```

---

## IDE Configuration

### VS Code Settings

**`.vscode/settings.json`:**
```json
{
    "editor.formatOnSave": true,
    "editor.defaultFormatter": "esbenp.prettier-vscode",
    "editor.codeActionsOnSave": {
        "source.fixAll.eslint": true
    },
    "[php]": {
        "editor.defaultFormatter": "bmewburn.vscode-intelephense-client",
        "editor.formatOnSave": false
    },
    "[blade]": {
        "editor.defaultFormatter": "shufo.vscode-blade-formatter"
    },
    "intelephense.format.braces": "psr12",
    "php.validate.executablePath": "/usr/bin/php",
    "phpstan.enabled": true,
    "phpstan.level": "8"
}
```

### PHPStorm Settings

**Laravel IDE Helper:**
```bash
composer require --dev barryvdh/laravel-ide-helper
php artisan ide-helper:generate
php artisan ide-helper:models
php artisan ide-helper:meta
```

---

## Checklist перед коммитом

- [ ] Код соответствует PSR-12
- [ ] Все переменные имеют type hints
- [ ] PHPDoc блоки для public методов
- [ ] `declare(strict_types=1);` в начале PHP файлов
- [ ] Нет `dd()`, `var_dump()`, `console.log()` в коде
- [ ] ESLint не выдает ошибок
- [ ] Prettier отформатировал код
- [ ] PHPStan проходит на уровне 8
- [ ] Тесты проходят (`composer test`)
- [ ] Нет неиспользуемых импортов
- [ ] Нет закомментированного кода

---

## Дополнительные рекомендации

### Комментарии
- Код должен быть самодокументируемым
- Комментарии только для сложной логики
- PHPDoc для всех public методов и классов
- TODO комментарии с именем автора и датой

```php
/**
 * Calculate the discount amount based on user tier
 *
 * @param User $user The user to calculate discount for
 * @param int $amount The original amount in credits
 * @return int The discounted amount
 * @throws \InvalidArgumentException If amount is negative
 */
public function calculateDiscount(User $user, int $amount): int
{
    // TODO: Adam - 2025-10-19 - Implement tier-based discounts
    return $amount;
}
```

### Тестирование
- Unit тесты для сервисов
- Feature тесты для контроллеров
- Минимум 80% покрытия кода
- Тесты должны быть читаемыми

### Безопасность
- Валидация всех входных данных
- Использование Form Requests
- CSRF protection
- XSS protection (экранирование вывода)
- SQL injection защита (Eloquent)

---

## Заключение

Все эти стандарты и инструменты должны быть интегрированы в процесс разработки с **первого дня**. Автоматизация проверок через Git hooks и CI/CD гарантирует, что весь код в репозитории соответствует стандартам.

**Главное правило:** Код должен быть чистым, читаемым и поддерживаемым. Мы пишем код для людей, не для машин.
