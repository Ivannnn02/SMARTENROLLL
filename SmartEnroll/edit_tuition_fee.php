<?php
declare(strict_types=1);

require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/enrollment_form_config.php';

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

smartenroll_require_role('finance');

function smartenroll_parse_tuition_fee_value(mixed $value): float
{
    $raw = trim((string)$value);
    if ($raw === '') {
        throw new RuntimeException('Each grade level needs a tuition fee.');
    }

    $normalized = str_replace(',', '', $raw);
    if (!is_numeric($normalized)) {
        throw new RuntimeException('Tuition fee must be a valid number.');
    }

    $fee = round((float)$normalized, 2);
    if ($fee < 0) {
        throw new RuntimeException('Tuition fee cannot be negative.');
    }

    return $fee;
}

$successMessage = '';
$errorMessage = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['form_action'] ?? '') === 'save_tuition_fees') {
    try {
        $currentGradeLevels = smartenroll_get_grade_levels();
        $postedKeys = $_POST['grade_key'] ?? [];
        $postedFees = $_POST['tuition_fee'] ?? [];
        $postedFeeMap = [];
        $max = max(count($postedKeys), count($postedFees));

        for ($i = 0; $i < $max; $i++) {
            $gradeKey = trim((string)($postedKeys[$i] ?? ''));
            if ($gradeKey === '') {
                continue;
            }

            $postedFeeMap[$gradeKey] = smartenroll_parse_tuition_fee_value($postedFees[$i] ?? '');
        }

        $saveRows = [];
        foreach ($currentGradeLevels as $row) {
            $gradeKey = (string)$row['grade_key'];

            $saveRows[] = [
                'grade_key' => $gradeKey,
                'grade_label' => (string)$row['grade_label'],
                'tuition_fee' => $postedFeeMap[$gradeKey] ?? (float)$row['tuition_fee'],
            ];
        }

        smartenroll_save_grade_levels($saveRows);
        smartenroll_sync_tuition_payment_totals();
        header('Location: edit_tuition_fee.php?status=saved');
        exit;
    } catch (Throwable $e) {
        $errorMessage = $e->getMessage();
    }
}

if (($_GET['status'] ?? '') === 'saved') {
    $successMessage = 'Tuition fees were updated successfully.';
}

try {
    $gradeLevels = smartenroll_get_grade_levels();
} catch (Throwable $e) {
    $gradeLevels = [];
    if ($errorMessage === '') {
        $errorMessage = $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>SMARTENROLL | Edit Tuition Fee</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/editable_enrollment_form.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body class="dashboard-page dashboard-white-page">

<main class="dashboard-main">
    <div class="dashboard-header student-header">
        <div class="student-header-left">
            <a href="dashboard.php" class="dashboard-link back-left">
                <i class="fa-solid fa-arrow-left"></i>
            </a>
            <div class="student-header-title">
                <h1>Edit Tuition Fee</h1>
                <p>Update the tuition fee amount for each grade level.</p>
            </div>
        </div>
    </div>

    <div class="settings-card">
        <?php if ($successMessage !== ''): ?>
            <div class="settings-alert success"><?php echo htmlspecialchars($successMessage); ?></div>
        <?php endif; ?>

        <?php if ($errorMessage !== ''): ?>
            <div class="settings-alert error"><?php echo htmlspecialchars($errorMessage); ?></div>
        <?php endif; ?>

        <div class="settings-intro">
            <h2>Tuition Fees</h2>
            <p>Change the amount for each active grade level. Grade names are managed in Edit Enrollment Form.</p>
        </div>

        <form method="post">
            <input type="hidden" name="form_action" value="save_tuition_fees">

            <div class="settings-subsection">
                <h3 class="detail-section-title">Grade Level Tuition Fee</h3>
                <div class="settings-table-wrap">
                    <table class="settings-table">
                        <thead>
                            <tr>
                                <th>Grade Level</th>
                                <th>Tuition Fee</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($gradeLevels as $row): ?>
                                <tr>
                                    <td>
                                        <input type="hidden" name="grade_key[]" value="<?php echo htmlspecialchars((string)$row['grade_key']); ?>">
                                        <input type="text" value="<?php echo htmlspecialchars((string)$row['grade_label']); ?>" readonly>
                                    </td>
                                    <td>
                                        <input
                                            type="number"
                                            name="tuition_fee[]"
                                            value="<?php echo htmlspecialchars(number_format((float)$row['tuition_fee'], 2, '.', '')); ?>"
                                            min="0"
                                            step="0.01"
                                            inputmode="decimal"
                                            required
                                        >
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <div class="settings-actions">
                    <span class="settings-help">Use numbers only. Example: `72740.00`</span>
                    <button type="submit" class="settings-save-btn">Save Tuition Fees</button>
                </div>
            </div>
        </form>
    </div>
</main>
</body>
</html>
