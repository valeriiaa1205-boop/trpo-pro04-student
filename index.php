<?php
declare(strict_types=1);

require_once __DIR__ . '/functions.php';

$version = '1.0.0';
$today = date('d.m.Y');

$employees = loadEmployees(__DIR__ . '/employees.csv');
$formCases = loadFormCases(__DIR__ . '/form_cases.json');

$values = [
    'name' => '',
    'department' => '',
    'experience' => '',
    'email' => '',
];

$errors = [];
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    foreach ($values as $key => $_) {
        $values[$key] = trim((string)($_POST[$key] ?? ''));
    }

    $errors = validateInput($values);

    if ($errors === []) {
        $success = true;

        $employees[] = [
            'id' => count($employees) + 1,
            'name' => $values['name'],
            'department' => $values['department'],
            'experience' => (int)$values['experience'],
            'email' => $values['email'],
        ];

        $values = [
            'name' => '',
            'department' => '',
            'experience' => '',
            'email' => '',
        ];
    }
}

$departmentFilter = trim((string)($_GET['department'] ?? ''));
$visibleEmployees = $employees;

if ($departmentFilter !== '') {
    $visibleEmployees = array_values(array_filter(
        $employees,
        static fn(array $employee): bool =>
            $employee['department'] === $departmentFilter
    ));
}

$departments = array_values(array_unique(array_map(
    static fn(array $employee): string => $employee['department'],
    $employees
)));

foreach (['Разработка', 'Аналитика', 'Тестирование', 'Поддержка',
