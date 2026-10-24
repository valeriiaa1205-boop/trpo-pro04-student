<?php
declare(strict_types=1);

/**
 * Экранирует данные перед выводом в HTML.
 */
function e(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

/**
 * Читает сотрудников из CSV и возвращает массив ассоциативных записей.
 */
function loadEmployees(string $filePath): array
{
    if (!is_readable($filePath)) {
        return [];
    }

    $rows = [];
    $handle = fopen($filePath, 'r');

    if ($handle === false) {
        return [];
    }

    $headers = fgetcsv($handle, 0, ';', '"', '');
    if (!is_array($headers)) {
        fclose($handle);
        return [];
    }

    while (($values = fgetcsv($handle, 0, ';', '"', '')) !== false) {
        if (count($values) !== count($headers)) {
            continue;
        }

        $row = array_combine($headers, $values);
        if ($row !== false) {
            $rows[] = normalizeEmployee($row);
        }
    }

    fclose($handle);
    return $rows;
}

/**
 * Приводит одну строку CSV к удобному для приложения виду.
 */
function normalizeEmployee(array $row): array
{
    return [
        'id' => (int)($row['id'] ?? 0),
        'name' => trim((string)($row['name'] ?? '')),
        'department' => trim((string)($row['department'] ?? '')),
        'experience' => (int)($row['experience'] ?? 0),
        'email' => trim((string)($row['email'] ?? '')),
    ];
}

/**
 * Проверяет данные формы и возвращает список ошибок.
 */
function validateInput(array $input): array
{
    $errors = [];

    $name = trim((string)($input['name'] ?? ''));
    $department = trim((string)($input['department'] ?? ''));
    $experienceRaw = trim((string)($input['experience'] ?? ''));
    $email = trim((string)($input['email'] ?? ''));

    if ($name === '') {
        $errors['name'] = 'Укажите имя и фамилию.';
    } elseif (mb_strlen($name) > 100) {
        $errors['name'] = 'Имя не должно быть длиннее 100 символов.';
    }

    if ($department === '') {
        $errors['department'] = 'Выберите отдел.';
    }

    if ($experienceRaw === '' || filter_var($experienceRaw, FILTER_VALIDATE_INT) === false) {
        $errors['experience'] = 'Укажите целое число лет опыта.';
    } else {
        $experience = (int)$experienceRaw;
        if ($experience < 0 || $experience > 60) {
            $errors['experience'] = 'Опыт должен быть от 0 до 60 лет.';
        }
    }

    if ($email === '' || filter_var($email, FILTER_VALIDATE_EMAIL) === false) {
        $errors['email'] = 'Введите корректный email.';
    }

    return $errors;
}

/**
 * Читает тестовые сценарии из JSON, чтобы использовать dataset в проверке.
 */
function loadFormCases(string $filePath): array
{
    if (!is_readable($filePath)) {
        return [];
    }

    $content = file_get_contents($filePath);
    if ($content === false) {
        return [];
    }

    $decoded = json_decode($content, true);
    return is_array($decoded) ? $decoded : [];
}
