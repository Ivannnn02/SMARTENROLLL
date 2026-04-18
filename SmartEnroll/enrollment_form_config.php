<?php
declare(strict_types=1);

require_once __DIR__ . '/auth.php';

function smartenroll_grade_level_defaults(): array
{
    return [
        ['grade_key' => 'Toddler', 'grade_label' => 'Toddler', 'tuition_fee' => 63340.00, 'sort_order' => 10],
        ['grade_key' => 'Casa', 'grade_label' => 'Casa', 'tuition_fee' => 69732.00, 'sort_order' => 20],
        ['grade_key' => 'Kindergarten', 'grade_label' => 'Kindergarten', 'tuition_fee' => 71612.00, 'sort_order' => 30],
        ['grade_key' => 'Brave', 'grade_label' => 'Brave SpEd', 'tuition_fee' => 79226.00, 'sort_order' => 40],
        ['grade_key' => 'Grade 1', 'grade_label' => 'Grade 1', 'tuition_fee' => 72740.00, 'sort_order' => 50],
        ['grade_key' => 'Grade 2', 'grade_label' => 'Grade 2', 'tuition_fee' => 72740.00, 'sort_order' => 60],
        ['grade_key' => 'Grade 3', 'grade_label' => 'Grade 3', 'tuition_fee' => 74240.00, 'sort_order' => 70],
    ];
}

function smartenroll_grade_breakdown_templates(): array
{
    return [
        'Toddler' => [
            'Tuition Fee' => 57340.00,
            'Registration Fee & Miscellaneous' => 6000.00,
        ],
        'Casa' => [
            'Tuition Fee' => 69732.00,
        ],
        'Brave' => [
            'Tuition Fee' => 73226.00,
            'Registration Fee & Miscellaneous' => 6000.00,
        ],
        'Kindergarten' => [
            'Tuition Fee' => 65612.00,
            'Registration Fee & Miscellaneous' => 6000.00,
        ],
        'Grade 1' => [
            'Tuition Fee' => 66740.00,
            'Registration Fee & Miscellaneous' => 2500.00,
            'Books' => 3500.00,
        ],
        'Grade 2' => [
            'Tuition Fee' => 66740.00,
            'Registration Fee & Miscellaneous' => 2000.00,
            'Books' => 4000.00,
        ],
        'Grade 3' => [
            'Tuition Fee' => 66740.00,
            'Registration Fee & Miscellaneous' => 1000.00,
            'Books' => 5000.00,
        ],
    ];
}

function smartenroll_grade_level_connection(?mysqli $conn, bool &$ownsConnection): mysqli
{
    if ($conn instanceof mysqli) {
        $ownsConnection = false;
        return $conn;
    }

    $ownsConnection = true;
    return smartenroll_auth_db();
}

function smartenroll_ensure_grade_levels_table(mysqli $conn): void
{
    $conn->query(
        "CREATE TABLE IF NOT EXISTS enrollment_grade_levels (
            id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
            grade_key VARCHAR(150) NOT NULL,
            grade_label VARCHAR(150) NOT NULL,
            tuition_fee DECIMAL(12,2) NOT NULL DEFAULT 0.00,
            sort_order INT NOT NULL DEFAULT 0,
            is_active TINYINT(1) NOT NULL DEFAULT 1,
            created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
            UNIQUE KEY uniq_grade_key (grade_key)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci"
    );
}

function smartenroll_seed_grade_levels(mysqli $conn): void
{
    $countResult = $conn->query("SELECT COUNT(*) AS total FROM enrollment_grade_levels");
    $count = (int)($countResult->fetch_assoc()['total'] ?? 0);
    if ($count > 0) {
        return;
    }

    $stmt = $conn->prepare(
        "INSERT INTO enrollment_grade_levels (grade_key, grade_label, tuition_fee, sort_order, is_active)
         VALUES (?, ?, ?, ?, 1)"
    );

    foreach (smartenroll_grade_level_defaults() as $row) {
        $gradeKey = (string)$row['grade_key'];
        $gradeLabel = (string)$row['grade_label'];
        $tuitionFee = (float)$row['tuition_fee'];
        $sortOrder = (int)$row['sort_order'];
        $stmt->bind_param('ssdi', $gradeKey, $gradeLabel, $tuitionFee, $sortOrder);
        $stmt->execute();
    }

    $stmt->close();

    smartenroll_restore_toddler_grade_level($conn);
}

function smartenroll_restore_toddler_grade_level(mysqli $conn): void
{
    $toddlerRow = null;
    foreach (smartenroll_grade_level_defaults() as $row) {
        if (((string)($row['grade_key'] ?? '')) === 'Toddler') {
            $toddlerRow = $row;
            break;
        }
    }

    if ($toddlerRow === null) {
        return;
    }

    $stmt = $conn->prepare(
        "INSERT INTO enrollment_grade_levels (grade_key, grade_label, tuition_fee, sort_order, is_active)
         VALUES (?, ?, ?, ?, 1)
         ON DUPLICATE KEY UPDATE
            grade_label = VALUES(grade_label),
            tuition_fee = VALUES(tuition_fee),
            sort_order = VALUES(sort_order),
            is_active = 1"
    );

    $gradeKey = (string)$toddlerRow['grade_key'];
    $gradeLabel = (string)$toddlerRow['grade_label'];
    $tuitionFee = (float)$toddlerRow['tuition_fee'];
    $sortOrder = (int)$toddlerRow['sort_order'];
    $stmt->bind_param('ssdi', $gradeKey, $gradeLabel, $tuitionFee, $sortOrder);
    $stmt->execute();
    $stmt->close();
}

function smartenroll_get_grade_levels(?mysqli $conn = null): array
{
    $ownsConnection = false;
    $db = smartenroll_grade_level_connection($conn, $ownsConnection);

    try {
        smartenroll_ensure_grade_levels_table($db);
        smartenroll_seed_grade_levels($db);
        smartenroll_restore_toddler_grade_level($db);

        $rows = [];
        $result = $db->query(
            "SELECT id, grade_key, grade_label, tuition_fee, sort_order, is_active
             FROM enrollment_grade_levels
             WHERE is_active = 1
             ORDER BY sort_order ASC, id ASC"
        );

        while ($row = $result->fetch_assoc()) {
            $row['id'] = (int)($row['id'] ?? 0);
            $row['tuition_fee'] = round((float)($row['tuition_fee'] ?? 0), 2);
            $row['sort_order'] = (int)($row['sort_order'] ?? 0);
            $row['is_active'] = (int)($row['is_active'] ?? 0);
            $rows[] = $row;
        }

        return $rows;
    } finally {
        if ($ownsConnection) {
            $db->close();
        }
    }
}

function smartenroll_save_grade_levels(array $rows, ?mysqli $conn = null): void
{
    $ownsConnection = false;
    $db = smartenroll_grade_level_connection($conn, $ownsConnection);

    try {
        smartenroll_ensure_grade_levels_table($db);

        $cleanRows = [];
        $seenKeys = [];
        $seenLabels = [];
        $sortOrder = 10;

        foreach ($rows as $row) {
            $gradeKey = trim((string)($row['grade_key'] ?? ''));
            $gradeLabel = trim((string)($row['grade_label'] ?? ''));
            $tuitionFee = round((float)($row['tuition_fee'] ?? 0), 2);

            if ($gradeKey === '' && $gradeLabel === '') {
                continue;
            }

            // If one field is blank, reuse the other so admins can add or rename grades faster.
            if ($gradeKey === '' && $gradeLabel !== '') {
                $gradeKey = $gradeLabel;
            }

            if ($gradeLabel === '' && $gradeKey !== '') {
                $gradeLabel = $gradeKey;
            }

            if (isset($seenKeys[strtolower($gradeKey)])) {
                throw new RuntimeException('Duplicate grade values are not allowed: ' . $gradeKey);
            }
            $seenKeys[strtolower($gradeKey)] = true;

            if (isset($seenLabels[strtolower($gradeLabel)])) {
                throw new RuntimeException('This grade level already exists: ' . $gradeLabel);
            }
            $seenLabels[strtolower($gradeLabel)] = true;

            $cleanRows[] = [
                'grade_key' => $gradeKey,
                'grade_label' => $gradeLabel,
                'tuition_fee' => $tuitionFee,
                'sort_order' => $sortOrder,
            ];
            $sortOrder += 10;
        }

        if (empty($cleanRows)) {
            throw new RuntimeException('Add at least one grade level before saving.');
        }

        $db->begin_transaction();
        $db->query("DELETE FROM enrollment_grade_levels");

        $stmt = $db->prepare(
            "INSERT INTO enrollment_grade_levels (grade_key, grade_label, tuition_fee, sort_order, is_active)
             VALUES (?, ?, ?, ?, 1)"
        );

        foreach ($cleanRows as $row) {
            $gradeKey = $row['grade_key'];
            $gradeLabel = $row['grade_label'];
            $tuitionFee = $row['tuition_fee'];
            $rowSortOrder = $row['sort_order'];
            $stmt->bind_param('ssdi', $gradeKey, $gradeLabel, $tuitionFee, $rowSortOrder);
            $stmt->execute();
        }

        $stmt->close();
        $db->commit();
    } catch (Throwable $e) {
        if ($db->errno === 0) {
            try {
                $db->rollback();
            } catch (Throwable $ignore) {
            }
        } else {
            try {
                $db->rollback();
            } catch (Throwable $ignore) {
            }
        }
        throw $e;
    } finally {
        if ($ownsConnection) {
            $db->close();
        }
    }
}

function smartenroll_get_grade_tuition_map(?mysqli $conn = null): array
{
    $map = [];
    foreach (smartenroll_get_grade_levels($conn) as $row) {
        $map[(string)$row['grade_key']] = (float)$row['tuition_fee'];
    }

    return $map;
}

function smartenroll_get_grade_breakdown_map(?mysqli $conn = null): array
{
    $tuitionMap = smartenroll_get_grade_tuition_map($conn);
    $templates = smartenroll_grade_breakdown_templates();
    $result = [];

    foreach ($tuitionMap as $gradeKey => $tuitionFee) {
        $template = $templates[$gradeKey] ?? ['Tuition Fee' => $tuitionFee];
        $fixedTotal = 0.0;

        foreach ($template as $label => $amount) {
            if ($label !== 'Tuition Fee') {
                $fixedTotal += (float)$amount;
            }
        }

        if ($fixedTotal >= $tuitionFee) {
            $result[$gradeKey] = ['Tuition Fee' => $tuitionFee];
            continue;
        }

        $template['Tuition Fee'] = round($tuitionFee - $fixedTotal, 2);
        $result[$gradeKey] = $template;
    }

    return $result;
}
