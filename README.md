## **Настройка непрерывной интеграции с использованием GitHub Actions и Docker**

---

## Цель работы
Научиться:
- Разрабатывать web-приложение на PHP;
- Писать юнит-тесты для него;
- Использовать Docker для контейнеризации;
- Настраивать CI-процессы с GitHub Actions.

---

## Задание
1. Разработать PHP-приложение с использованием SQLite.
2. Написать unit-тесты для компонентов приложения.
3. Настроить процесс CI через GitHub Actions с использованием Docker.

---

## Выполнение работы

### Структура проекта

```bash
containers08/
├── .github/
│   └── workflows/
│       └── main.yml
├── site/
│   ├── modules/
│   │   ├── database.php
│   │   └── page.php
│   ├── templates/
│   │   └── index.tpl
│   ├── styles/
│   │   └── style.css
│   ├── config.php
│   └── index.php
├── sql/
│   └── schema.sql
├── tests/
│   ├── testframework.php
│   └── tests.php
├── Dockerfile
└── README.md
```

---

### Основные файлы

#### `database.php` (класс Database)
- Методы:
  - `__construct($path)`
  - `Execute($sql)`
  - `Fetch($sql)`
  - `Create($table, $data)`
  - `Read($table, $id)`
  - `Update($table, $id, $data)`
  - `Delete($table, $id)`
  - `Count($table)`

#### `page.php` (класс Page)
- Методы:
  - `__construct($template)`
  - `Render($data)`

#### `config.php`
```php
<?php
$config = [
    "db" => [
        "path" => "/var/www/db/db.sqlite"
    ]
];
```

#### `index.php`
```php
<?php
require_once __DIR__ . '/modules/database.php';
require_once __DIR__ . '/modules/page.php';
require_once __DIR__ . '/config.php';

$db = new Database($config["db"]["path"]);
$page = new Page(__DIR__ . '/templates/index.tpl');

$pageId = $_GET['page'] ?? 1;
$data = $db->Read("page", $pageId);

echo $page->Render($data);
```

---

### Тестирование

#### `tests/testframework.php`
- Простейший фреймворк с функциями `assertExpression`, `info`, `error`.

#### `tests/tests.php`
- Добавьте тесты для всех методов класса `Database` и `Page`.

Пример теста:
```php
function testDbConnection() {
    global $config;
    try {
        $db = new Database($config['db']['path']);
        return assertExpression($db !== null, "DB connected", "DB failed");
    } catch (Exception $e) {
        return assertExpression(false, "", "DB Exception: " . $e->getMessage());
    }
}
```

---

### Dockerfile
```Dockerfile
FROM php:8.4-fpm

RUN apt-get update && apt-get install -y sqlite3 libsqlite3-dev && docker-php-ext-install pdo_sqlite

VOLUME ["/var/www/db"]

COPY sql/schema.sql /var/www/db/schema.sql

RUN cat /var/www/db/schema.sql | sqlite3 /var/www/db/db.sqlite && \
    chmod 777 /var/www/db/db.sqlite && \
    rm /var/www/db/schema.sql

COPY site /var/www/html
```

---

### GitHub Actions

#### `.github/workflows/main.yml`

```yaml
name: CI

on:
  push:
    branches: [ main ]

jobs:
  build:
    runs-on: ubuntu-latest
    steps:
      - uses: actions/checkout@v4

      - name: Build Docker image
        run: docker build -t containers08 .

      - name: Create container
        run: docker create --name container --volume database:/var/www/db containers08

      - name: Copy tests
        run: docker cp ./tests container:/var/www/html

      - name: Start container
        run: docker start container

      - name: Run tests
        run: docker exec container php /var/www/html/tests/tests.php

      - name: Stop container
        run: docker stop container

      - name: Remove container
        run: docker rm container

      - name: Remove Docker image 
        run: docker rmi containers08
```

---

## Ответы на вопросы

### Что такое непрерывная интеграция?
**Непрерывная интеграция (CI)** — это практика регулярной интеграции изменений кода в центральный репозиторий, после чего автоматически выполняются сборка и тестирование, чтобы выявить ошибки на ранних этапах разработки.

---

### Для чего нужны юнит-тесты? Как часто их нужно запускать?
**Юнит-тесты** проверяют корректность работы отдельных компонентов программы (функций, методов).
- Нужны для автоматической проверки правильности работы логики.
- Запускаются каждый раз при любом изменении кода (в идеале — при каждом коммите или pull request).

---

### Что изменить в `.github/workflows/main.yml`, чтобы тесты запускались при Pull Request?
Добавить триггер `pull_request`:
```yaml
on:
  push:
    branches: [ main ]
  pull_request:   # это добавление
```

---

### Что добавить в `.github/workflows/main.yml`, чтобы удалять созданные образы после выполнения тестов?
Добавить шаг в конце:
```yaml
- name: Remove Docker image
  run: docker rmi containers08
```

---

## Выводы
- Настроена система непрерывной интеграции через GitHub Actions.
- Создано простое PHP-приложение с подключением к SQLite.
- Написаны и интегрированы unit-тесты.
- Контейнеризация упростила настройку окружения и запуск CI/CD пайплайна.

---
