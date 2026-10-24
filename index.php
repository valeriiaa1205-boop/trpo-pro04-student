<?php
declare(strict_types=1);

require_once __DIR__ . '/../src/functions.php';

$version = '1.0.0';
$today = date('d.m.Y');
$employees = loadEmployees(__DIR__ . '/../data/employees.csv');
$formCases = loadFormCases(__DIR__ . '/../data/form_cases.json');

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
        $values = ['name' => '', 'department' => '', 'experience' => '', 'email' => ''];
    }
}

$departmentFilter = trim((string)($_GET['department'] ?? ''));
$visibleEmployees = $employees;
if ($departmentFilter !== '') {
    $visibleEmployees = array_values(array_filter(
        $employees,
        static fn(array $employee): bool => $employee['department'] === $departmentFilter
    ));
}

$departments = array_values(array_unique(array_map(
    static fn(array $employee): string => $employee['department'],
    $employees
)));
sort($departments);

$initials = static function (string $name): string {
    $parts = preg_split('/\s+/u', trim($name)) ?: [];
    $letters = '';
    foreach (array_slice($parts, 0, 2) as $part) {
        $letters .= mb_substr($part, 0, 1);
    }
    return mb_strtoupper($letters);
};
?>
<!doctype html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Практическая работа 4 — Панель сотрудников</title>
    <link rel="stylesheet" href="custom.css">
</head>
<body>
<header class="topbar">
    <div class="topbar__inner">
        <a class="brand" href="index.php">Практическая работа 4</a>
        <span class="topbar__tag">ТРПО</span>
    </div>
</header>

<main class="container page">
    <section class="hero">
        <div>
            <span class="pill">PHP · GET · POST</span>
            <h1>Панель сотрудников</h1>
            <p class="muted">Форма обработки данных и список сотрудников из dataset.</p>
        </div>
        <div class="hero__meta">
            <span>Версия <?= e($version) ?></span>
            <strong><?= e($today) ?></strong>
        </div>
    </section>

    <section class="stats" aria-label="Показатели">
        <article class="panel stat">
            <span class="stat__label">Всего сотрудников</span>
            <strong><?= count($employees) ?></strong>
            <span class="stat__note">Записи из CSV и текущая форма</span>
        </article>
        <article class="panel stat">
            <span class="stat__label">Отделов</span>
            <strong><?= count($departments) ?></strong>
            <span class="stat__note">Разные направления работы</span>
        </article>
        <article class="panel stat">
            <span class="stat__label">Тест-кейсов</span>
            <strong><?= count($formCases) ?></strong>
            <span class="stat__note">Сценарии из form_cases.json</span>
        </article>
    </section>

    <?php if ($success): ?>
        <div class="notice notice--success" role="status">
            <strong>Сотрудник добавлен.</strong> Данные прошли проверку и отображены в таблице.
        </div>
    <?php endif; ?>

    <div class="content-grid">
        <section class="panel form-panel">
            <div class="section-heading">
                <div>
                    <span class="eyebrow">Новая запись</span>
                    <h2>Добавить сотрудника</h2>
                </div>
            </div>
            <p class="muted">Заполни все поля. При ошибке введённые значения сохранятся.</p>

            <?php if ($errors !== []): ?>
                <div class="notice notice--error" role="alert">
                    <strong>Проверь форму:</strong>
                    <ul>
                        <?php foreach ($errors as $message): ?>
                            <li><?= e($message) ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <form method="post" action="index.php" novalidate>
                <div class="field">
                    <label for="name">Имя и фамилия</label>
                    <input id="name" name="name" type="text" maxlength="100"
                           value="<?= e($values['name']) ?>" placeholder="Например, Иван Петров"
                           class="<?= isset($errors['name']) ? 'is-invalid' : '' ?>" required>
                    <?php if (isset($errors['name'])): ?><small class="field-error"><?= e($errors['name']) ?></small><?php endif; ?>
                </div>

                <div class="field">
                    <label for="department">Отдел</label>
                    <select id="department" name="department" class="<?= isset($errors['department']) ? 'is-invalid' : '' ?>" required>
                        <option value="">Выбери отдел</option>
                        <?php foreach (['Разработка', 'Аналитика', 'Тестирование', 'Поддержка', 'Другое'] as $department): ?>
                            <option value="<?= e($department) ?>" <?= $values['department'] === $department ? 'selected' : '' ?>>
                                <?= e($department) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <?php if (isset($errors['department'])): ?><small class="field-error"><?= e($errors['department']) ?></small><?php endif; ?>
                </div>

                <div class="field">
                    <label for="experience">Опыт работы, лет</label>
                    <input id="experience" name="experience" type="number" min="0" max="60"
                           value="<?= e($values['experience']) ?>" placeholder="Например, 2"
                           class="<?= isset($errors['experience']) ? 'is-invalid' : '' ?>" required>
                    <?php if (isset($errors['experience'])): ?><small class="field-error"><?= e($errors['experience']) ?></small><?php endif; ?>
                </div>

                <div class="field">
                    <label for="email">Электронная почта</label>
                    <input id="email" name="email" type="email" maxlength="150"
                           value="<?= e($values['email']) ?>" placeholder="name@example.com"
                           class="<?= isset($errors['email']) ? 'is-invalid' : '' ?>" required>
                    <?php if (isset($errors['email'])): ?><small class="field-error"><?= e($errors['email']) ?></small><?php endif; ?>
                </div>

                <button class="button" type="submit">Добавить сотрудника</button>
            </form>
        </section>

        <section class="panel table-panel">
            <div class="section-heading section-heading--wrap">
                <div>
                    <span class="eyebrow">Dataset</span>
                    <h2>Список сотрудников</h2>
                </div>
                <span class="count"><?= count($visibleEmployees) ?> записей</span>
            </div>

            <form class="filter" method="get" action="index.php">
                <label for="department-filter">Фильтр по отделу</label>
                <div class="filter__row">
                    <select id="department-filter" name="department">
                        <option value="">Все отделы</option>
                        <?php foreach ($departments as $department): ?>
                            <option value="<?= e($department) ?>" <?= $departmentFilter === $department ? 'selected' : '' ?>>
                                <?= e($department) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <button class="button button--secondary" type="submit">Фильтровать</button>
                    <?php if ($departmentFilter !== ''): ?><a class="clear-link" href="index.php">Сбросить</a><?php endif; ?>
                </div>
            </form>

            <div class="table-wrap">
                <table>
                    <thead><tr><th>Сотрудник</th><th>Отдел</th><th>Опыт</th><th>Email</th></tr></thead>
                    <tbody>
                    <?php if ($visibleEmployees === []): ?>
                        <tr><td colspan="4" class="empty">По этому фильтру сотрудников нет.</td></tr>
                    <?php else: ?>
                        <?php foreach ($visibleEmployees as $employee): ?>
                            <tr>
                                <td><span class="person"><span class="avatar"><?= e($initials($employee['name'])) ?></span><span><strong><?= e($employee['name']) ?></strong><small>ID <?= (int)$employee['id'] ?></small></span></span></td>
                                <td><span class="department-tag"><?= e($employee['department']) ?></span></td>
                                <td><?= (int)$employee['experience'] ?> лет</td>
                                <td><a class="email-link" href="mailto:<?= e($employee['email']) ?>"><?= e($employee['email']) ?></a></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>
            <p class="table-footnote">Исходные записи читаются из <code>data/employees.csv</code>.</p>
        </section>
    </div>

    <section class="panel checks">
        <div class="section-heading">
            <div><span class="eyebrow">Проверка</span><h2>Сценарии из form_cases.json</h2></div>
        </div>
        <div class="case-grid">
            <?php foreach ($formCases as $case): ?>
                <article class="case-card">
                    <span class="case-status <?= !empty($case['valid']) ? 'case-status--ok' : 'case-status--bad' ?>">
                        <?= !empty($case['valid']) ? 'Корректные данные' : 'Ожидается ошибка' ?>
                    </span>
                    <strong><?= e((string)($case['case'] ?? 'Сценарий')) ?></strong>
                    <span class="muted">Имя: <?= e((string)($case['name'] ?? '')) ?: 'не указано' ?></span>
                    <span class="muted">Опыт: <?= e((string)($case['experience'] ?? '')) ?> лет</span>
                </article>
            <?php endforeach; ?>
        </div>
    </section>

    <section class="note">
        <strong>Как это работает:</strong> форма отправляет данные методом POST, PHP проверяет значения и безопасно выводит их через <code>htmlspecialchars</code>. Фильтр отдела использует GET-параметр.
    </section>
</main>

<footer class="footer">
    <div class="footer__inner"><span>Практическая работа №4</span><span>PHP · HTML · CSV · JSON</span></div>
</footer>
</body>
</html>
