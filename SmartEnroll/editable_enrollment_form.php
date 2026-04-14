<?php
declare(strict_types=1);

require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/enrollment_form_config.php';

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

smartenroll_require_role('admin');

function smartenroll_editable_labelize(string $key): string
{
    $map = [
        'student_id' => 'Student ID',
        'school_year' => 'School Year',
        'completion_date' => 'Completion Date',
        'created_at' => 'Created At',
        'learner_lname' => 'Learner Last Name',
        'learner_fname' => 'Learner First Name',
        'learner_mname' => 'Learner Middle Name',
        'learner_ext' => 'Learner Extension Name',
        'mother_maiden' => 'Mother Maiden Full Name',
        'father_occ' => 'Father Occupation',
        'mother_occ' => 'Mother Occupation',
        'guardian_occ' => 'Guardian Occupation',
        'guardian_contact' => 'Guardian Contact Number',
        'father_contact' => 'Father Contact Number',
        'mother_contact' => 'Mother Contact Number',
        'emergency1_name' => 'Emergency 1 Name',
        'emergency1_contact' => 'Emergency 1 Contact',
        'emergency1_relationship' => 'Emergency 1 Relationship',
        'emergency2_name' => 'Emergency 2 Name',
        'emergency2_contact' => 'Emergency 2 Contact',
        'emergency2_relationship' => 'Emergency 2 Relationship',
        'emergency3_name' => 'Emergency 3 Name',
        'emergency3_contact' => 'Emergency 3 Contact',
        'emergency3_relationship' => 'Emergency 3 Relationship',
        'dob' => 'Date of Birth',
    ];

    if (isset($map[$key])) {
        return $map[$key];
    }

    $key = str_replace('_', ' ', $key);
    $key = preg_replace('/\s+/', ' ', $key);
    return ucwords(trim($key));
}

function smartenroll_editable_normalize_date(string $value): string
{
    if ($value === '') {
        return '';
    }

    $formats = ['Y-m-d', 'm/d/Y', 'd/m/Y'];
    foreach ($formats as $format) {
        $date = DateTime::createFromFormat($format, $value);
        if ($date instanceof DateTime) {
            return $date->format('Y-m-d');
        }
    }

    $timestamp = strtotime($value);
    return $timestamp !== false ? date('Y-m-d', $timestamp) : '';
}

function smartenroll_editable_input_type(string $column): string
{
    if (in_array($column, ['completion_date', 'dob'], true)) {
        return 'date';
    }

    if ($column === 'email') {
        return 'email';
    }

    if ($column === 'age') {
        return 'number';
    }

    if (str_contains($column, 'contact')) {
        return 'tel';
    }

    return 'text';
}

function smartenroll_editable_age_from_dob(string $value): string
{
    if ($value === '') {
        return '';
    }

    $normalized = smartenroll_editable_normalize_date($value);
    if ($normalized === '') {
        return '';
    }

    $dob = DateTime::createFromFormat('Y-m-d', $normalized);
    if (!($dob instanceof DateTime)) {
        return '';
    }

    $today = new DateTime('today');
    return (string)$today->diff($dob)->y;
}

function smartenroll_editable_sections(array $columns): array
{
    $sections = [
        'Enrollment Info' => [
            'student_id', 'grade_level', 'school_year', 'completion_date', 'created_at',
        ],
        'Learner Information' => [
            'learner_lname', 'learner_fname', 'learner_mname', 'learner_ext', 'nickname', 'sex', 'dob', 'age',
            'mother_tongue', 'religion', 'email',
        ],
        'Address Information' => [
            'province', 'municipality', 'barangay', 'street',
        ],
        'Father Information' => [
            'father_lname', 'father_fname', 'father_mname', 'father_occ', 'father_contact',
        ],
        'Mother Information' => [
            'mother_lname', 'mother_fname', 'mother_mname', 'mother_occ', 'mother_contact', 'mother_maiden',
        ],
        'Guardian Information' => [
            'guardian_type', 'guardian_lname', 'guardian_fname', 'guardian_mname', 'guardian_occ', 'guardian_contact',
        ],
        'Special Education Needs' => [
            'special_needs', 'medication', 'medication_details',
        ],
        'Emergency Contacts' => [
            'emergency1_name', 'emergency1_contact', 'emergency1_relationship',
            'emergency2_name', 'emergency2_contact', 'emergency2_relationship',
            'emergency3_name', 'emergency3_contact', 'emergency3_relationship',
        ],
    ];

    $mapped = ['id'];
    foreach ($sections as $fields) {
        foreach ($fields as $field) {
            $mapped[] = $field;
        }
    }

    $extras = [];
    foreach ($columns as $column) {
        if (!in_array($column, $mapped, true)) {
            $extras[] = $column;
        }
    }

    if ($extras !== []) {
        $sections['Other Saved Fields'] = $extras;
    }

    return $sections;
}

$editId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($editId > 0) {
    header('Location: student_edit.php?id=' . $editId . (isset($_GET['saved']) ? '&saved=' . urlencode((string)$_GET['saved']) : ''));
    exit;
}

$errorMessage = '';
$successMessage = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $rows = [];
        $gradeKeys = $_POST['grade_key'] ?? [];
        $gradeLabels = $_POST['grade_label'] ?? [];
        $tuitionFees = $_POST['tuition_fee'] ?? [];
        $max = max(count($gradeKeys), count($gradeLabels), count($tuitionFees));

        for ($i = 0; $i < $max; $i++) {
            $rows[] = [
                'grade_key' => $gradeKeys[$i] ?? '',
                'grade_label' => $gradeLabels[$i] ?? '',
                'tuition_fee' => $tuitionFees[$i] ?? '0',
            ];
        }

        smartenroll_save_grade_levels($rows);
        header('Location: editable_enrollment_form.php?status=saved');
        exit;
    } catch (Throwable $e) {
        $errorMessage = $e->getMessage();
    }
}

if (($_GET['status'] ?? '') === 'saved') {
    $successMessage = 'Enrollment form settings were saved successfully.';
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
    <title>SMARTENROLL | Editable Enrollment Form</title>
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
                <h1>Editable Enrollment Form</h1>
                <p>Manage the grade levels shown on the enrollment form and open student records for full editing.</p>
            </div>
        </div>
    </div>

    <div class="settings-card">
        <div class="settings-intro">
            <h2>Enrollment Form Setup</h2>
            <p>Add new grade levels, change the labels shown on the form, and update the tuition prices used in tuition pages.</p>
        </div>

        <?php if ($successMessage !== ''): ?>
            <div class="settings-alert success"><?php echo htmlspecialchars($successMessage); ?></div>
        <?php endif; ?>

        <?php if ($errorMessage !== ''): ?>
            <div class="settings-alert error"><?php echo htmlspecialchars($errorMessage); ?></div>
        <?php endif; ?>

        <form method="post" id="gradeSettingsForm">
            <div class="settings-table-wrap">
                <table class="settings-table">
                    <thead>
                        <tr>
                            <th>Stored Grade Value</th>
                            <th>Displayed Label</th>
                            <th>Tuition Fee</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody id="gradeSettingsBody">
                        <?php foreach ($gradeLevels as $row): ?>
                            <tr>
                                <td><input type="text" name="grade_key[]" value="<?php echo htmlspecialchars((string)$row['grade_key']); ?>" placeholder="Grade 4"></td>
                                <td><input type="text" name="grade_label[]" value="<?php echo htmlspecialchars((string)$row['grade_label']); ?>" placeholder="Grade 4"></td>
                                <td><input type="number" name="tuition_fee[]" value="<?php echo htmlspecialchars(number_format((float)$row['tuition_fee'], 2, '.', '')); ?>" min="0" step="0.01" placeholder="0.00"></td>
                                <td><button type="button" class="settings-row-remove" data-remove-row>Remove</button></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <div class="settings-actions">
                <button type="button" class="settings-add-btn" id="addGradeRow">Add Grade Level</button>
                <button type="submit" class="settings-save-btn">Save Form Settings</button>
            </div>

            <p class="settings-help">
                `Stored Grade Value` is what gets saved in student records. `Displayed Label` is what users see on the enrollment form.
            </p>
        </form>
    </div>
</main>

<script>
const gradeSettingsBody = document.getElementById('gradeSettingsBody');
const addGradeRowBtn = document.getElementById('addGradeRow');

function buildGradeRow() {
    const row = document.createElement('tr');
    row.innerHTML = `
        <td><input type="text" name="grade_key[]" placeholder="Grade 4"></td>
        <td><input type="text" name="grade_label[]" placeholder="Grade 4"></td>
        <td><input type="number" name="tuition_fee[]" min="0" step="0.01" placeholder="0.00"></td>
        <td><button type="button" class="settings-row-remove" data-remove-row>Remove</button></td>
    `;
    return row;
}

if (addGradeRowBtn && gradeSettingsBody) {
    addGradeRowBtn.addEventListener('click', () => {
        gradeSettingsBody.appendChild(buildGradeRow());
    });

    gradeSettingsBody.addEventListener('click', (event) => {
        const target = event.target;
        if (!(target instanceof HTMLElement) || !target.hasAttribute('data-remove-row')) {
            return;
        }

        const row = target.closest('tr');
        if (row && gradeSettingsBody.children.length > 1) {
            row.remove();
        }
    });
}
</script>
</body>
</html>
