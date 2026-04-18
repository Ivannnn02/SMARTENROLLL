<?php
declare(strict_types=1);

require_once __DIR__ . '/enrollment_form_config.php';
require_once __DIR__ . '/enrollment_fields.php';
require_once __DIR__ . '/auth.php';

smartenroll_require_role('finance');

$gradeLevels = smartenroll_get_grade_levels();
$customFieldsBySection = smartenroll_custom_fields_by_section();
$customFieldMap = smartenroll_get_field_label_map();
?>
<!DOCTYPE html>
<html lang="en">
<head>
        <meta charset="UTF-8">
        <title>SmartEnroll | Enrollment</title>
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <meta name="description" content="Online enrollment form for Adreo Montessori Inc. Submit learner, parent, guardian, and emergency contact details through SMARTENROLL.">
        <meta name="keywords" content="Adreo Montessori enrollment form, SMARTENROLL form, school application, student registration">
        <meta name="robots" content="index, follow">
        <meta property="og:type" content="website">
        <meta property="og:title" content="SMARTENROLL | Enrollment Form">
        <meta property="og:description" content="Complete the Adreo Montessori Inc. enrollment form online through SMARTENROLL.">
        <meta property="og:image" content="assets/logo.png">

        <!-- FONT -->
        <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">

        <!-- ICONS -->
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">



        <link rel="stylesheet" href="css/enroll.css">

    </head>
    <body class="enroll-body">

    <!-- FORM -->
   <main class="enroll-form">
    <div class="enroll-page-header">
        <div class="enroll-header-left">
            <a href="dashboard.php" class="icon-back" title="Go Back" aria-label="Back to dashboard">
                <i class="fas fa-arrow-left"></i>
            </a>
            <div class="enroll-header-title">
                <h1>Enrollment Form</h1>
                <p>Enter the learner, parent, guardian, and emergency contact details to complete a new enrollment.</p>
            </div>
        </div>
    </div>

<form id="enrollmentForm" action="save_enrollment.php" method="POST">

    <div class="form-top">
        <div class="completion-date">
    <label>Date of Completion:</label>
    <input type="date" id="completionDate" name="completion_date">
</div>
    </div>

    <?php if (!empty($customFieldsBySection['Enrollment Info'])): ?>
        <section class="form-section">
            <h2>Enrollment Info</h2>
            <div class="form-grid two">
                <?php foreach ($customFieldsBySection['Enrollment Info'] as $field): ?>
                    <?php $fieldKey = (string)$field['field_key']; ?>
                    <div class="form-group">
                        <label><?php echo htmlspecialchars(smartenroll_field_labelize($fieldKey, $customFieldMap)); ?></label>
                        <?php if (smartenroll_input_type_for($fieldKey, $customFieldMap) === 'select'): ?>
                            <select name="<?php echo htmlspecialchars($fieldKey); ?>">
                                <option value="">Select</option>
                                <?php foreach (smartenroll_custom_field_options($field) as $option): ?>
                                    <option value="<?php echo htmlspecialchars($option); ?>"><?php echo htmlspecialchars($option); ?></option>
                                <?php endforeach; ?>
                            </select>
                        <?php elseif (smartenroll_input_type_for($fieldKey, $customFieldMap) === 'textarea'): ?>
                            <textarea name="<?php echo htmlspecialchars($fieldKey); ?>" placeholder="<?php echo htmlspecialchars(smartenroll_field_labelize($fieldKey, $customFieldMap)); ?>"></textarea>
                        <?php else: ?>
                            <input type="<?php echo htmlspecialchars(smartenroll_input_type_for($fieldKey, $customFieldMap)); ?>" name="<?php echo htmlspecialchars($fieldKey); ?>" placeholder="<?php echo htmlspecialchars(smartenroll_field_labelize($fieldKey, $customFieldMap)); ?>">
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>
        </section>
    <?php endif; ?>



        <!-- GRADE LEVEL -->
        <section class="form-section">
        
            <h2>A. Grade Level</h2>

            <div class="ch-grid">
                <?php foreach ($gradeLevels as $gradeLevel): ?>
                    <label class="grade-level-option">
                        <input type="radio" name="grade_level" value="<?php echo htmlspecialchars((string)$gradeLevel['grade_key']); ?>">
                        <span class="grade-level-button"><?php echo htmlspecialchars((string)$gradeLevel['grade_label']); ?></span>
                    </label>
                <?php endforeach; ?>
            </div>

            <?php if (!empty($customFieldsBySection['Grade Level'])): ?>
                <?php $gradeLevelGridClass = count($customFieldsBySection['Grade Level']) === 1 ? 'one' : 'two'; ?>
                <div class="form-grid <?php echo $gradeLevelGridClass; ?>">
                    <?php foreach ($customFieldsBySection['Grade Level'] as $field): ?>
                        <?php $fieldKey = (string)$field['field_key']; ?>
                        <div class="form-group">
                            <label><?php echo htmlspecialchars(smartenroll_field_labelize($fieldKey, $customFieldMap)); ?></label>
                            <?php if (smartenroll_input_type_for($fieldKey, $customFieldMap) === 'select'): ?>
                                <select name="<?php echo htmlspecialchars($fieldKey); ?>">
                                    <option value="">Select</option>
                                    <?php foreach (smartenroll_custom_field_options($field) as $option): ?>
                                        <option value="<?php echo htmlspecialchars($option); ?>"><?php echo htmlspecialchars($option); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            <?php elseif (smartenroll_input_type_for($fieldKey, $customFieldMap) === 'textarea'): ?>
                                <textarea name="<?php echo htmlspecialchars($fieldKey); ?>" placeholder="<?php echo htmlspecialchars(smartenroll_field_labelize($fieldKey, $customFieldMap)); ?>"></textarea>
                            <?php else: ?>
                                <input type="<?php echo htmlspecialchars(smartenroll_input_type_for($fieldKey, $customFieldMap)); ?>" name="<?php echo htmlspecialchars($fieldKey); ?>" placeholder="<?php echo htmlspecialchars(smartenroll_field_labelize($fieldKey, $customFieldMap)); ?>">
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </section>

        <!-- LEARNER INFO -->
        <section class="form-section">
            <h2>B. Learner’s Information</h2>

            <!-- ROW 1 -->
            <div class="form-grid four">
                <div class="form-group">
                    <label>Last Name</label>
                    <input type="text" placeholder="Last Name" name="learner_lname" >

                </div>

                <div class="form-group">
                    <label>First Name</label>
                    <input type="text" placeholder="First Name" name="learner_fname">

                </div>

                <div class="form-group">
                    <label>Middle Name</label>
                    <input type="text" placeholder="Middle Name" name="learner_mname">


                </div>

                <div class="form-group">
                    <label>Extension Name</label>
                <select name="learner_ext">
                        <option value="">None</option>
                        <option value="Jr">Jr.</option>
                        <option value="Sr">Sr.</option>
                        <option value="II">II</option>
                        <option value="III">III</option>
                    </select>
                </div>
            </div>

            <!-- ROW 2 -->
            <div class="form-grid one">
                <div class="form-group">
                    <label>Nickname</label>
                    <input type="text" placeholder="Nickname" name="nickname">

                </div>
    <div class="form-group">
            <label>Sex</label>
            <select name="sex">
                <option value="">Select</option>
                <option value="Male">Male</option>
                <option value="Female">Female</option>
            </select>
        </div>
            </div>

            <!-- ROW 3 -->
            <div class="form-grid two">
                <div class="form-group">
        <label>Date of Birth</label>

        <div class="date-wrapper">
        <input
            type="text"
            id="dob"
            name="dob"
            class="date-input"
            placeholder="MM / DD / YYYY"
            autocomplete="off"
        >
        <span class="calendar-icon" id="calendarBtn">
            <i class="fas fa-calendar-alt"></i>
        </span>
    <div class="custom-dob-picker" id="dobPicker">
        <div class="picker-header">
        <span id="monthLabel"></span>
        <span id="yearLabel"></span>
    </div>

        <div class="picker-columns">
            <div class="picker-column month-col"></div>
            <div class="picker-column year-col"></div>
        </div>

        <div class="day-grid"></div>
    </div>

    </div>

    </div>

                <div class="form-group">
                    <label>Age</label>
                    <input type="number" id="age" name="age" readonly>
                </div>
            </div>

            <!-- ROW 4 -->
            <div class="form-grid three">
                <div class="form-group">
                    <label>Mother Tongue</label>
                    <input type="text" placeholder="Mother Tongue" name="mother_tongue">
                </div>

                <div class="form-group">
                    <label>Religion</label>
                    <input type="text" placeholder="Religion" name="religion">
                </div>

                <div class="form-group">
                    <label>Email Address</label>
                    <input type="email"placeholder="Email Address" name="email">
                </div>
            </div>

            <?php if (!empty($customFieldsBySection['Learner Information'])): ?>
                <div class="form-grid two">
                    <?php foreach ($customFieldsBySection['Learner Information'] as $field): ?>
                        <?php $fieldKey = (string)$field['field_key']; ?>
                        <div class="form-group">
                            <label><?php echo htmlspecialchars(smartenroll_field_labelize($fieldKey, $customFieldMap)); ?></label>
                            <?php if (smartenroll_input_type_for($fieldKey, $customFieldMap) === 'select'): ?>
                                <select name="<?php echo htmlspecialchars($fieldKey); ?>">
                                    <option value="">Select</option>
                                    <?php foreach (smartenroll_custom_field_options($field) as $option): ?>
                                        <option value="<?php echo htmlspecialchars($option); ?>"><?php echo htmlspecialchars($option); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            <?php elseif (smartenroll_input_type_for($fieldKey, $customFieldMap) === 'textarea'): ?>
                                <textarea name="<?php echo htmlspecialchars($fieldKey); ?>" placeholder="<?php echo htmlspecialchars(smartenroll_field_labelize($fieldKey, $customFieldMap)); ?>"></textarea>
                            <?php else: ?>
                                <input type="<?php echo htmlspecialchars(smartenroll_input_type_for($fieldKey, $customFieldMap)); ?>" name="<?php echo htmlspecialchars($fieldKey); ?>" placeholder="<?php echo htmlspecialchars(smartenroll_field_labelize($fieldKey, $customFieldMap)); ?>">
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </section>
    <!-- ADDRESS INFO -->
    <section class="form-section">
        <h2>Address Information</h2>

        <!-- ROW 1 -->
        <div class="form-grid three">
            <div class="form-group">
                <label>Province</label>
               <select id="province" name="province_codes">
                    <option value="">Select Province</option>
                </select>
                <input type="hidden" name="province" id="province_name">
            </div>
        <div class="form-group">
            <label>Municipality / City</label>

                <select id="municipality" name="municipality_code" disabled>
                <option value="">Select Municipality</option>
            </select>

        <input type="hidden" name="municipality" id="municipality_name">
        </div>


            <div class="form-group">
                <label>Barangay</label>
                <select id="barangay" name="barangay" disabled>
                    <option value="">Select Barangay</option>
                </select>
            </div>
        </div>

        <!-- ROW 2 -->
        <div class="form-grid one">
            <div class="form-group">
                <label>House No. / Street / Bldg. / Subd.</label>
                <input type="text" name="street"placeholder="House No., Street Name">
            </div>
        </div>

        <?php if (!empty($customFieldsBySection['Address Information'])): ?>
            <div class="form-grid two">
                <?php foreach ($customFieldsBySection['Address Information'] as $field): ?>
                    <?php $fieldKey = (string)$field['field_key']; ?>
                    <div class="form-group">
                        <label><?php echo htmlspecialchars(smartenroll_field_labelize($fieldKey, $customFieldMap)); ?></label>
                        <?php if (smartenroll_input_type_for($fieldKey, $customFieldMap) === 'select'): ?>
                            <select name="<?php echo htmlspecialchars($fieldKey); ?>">
                                <option value="">Select</option>
                                <?php foreach (smartenroll_custom_field_options($field) as $option): ?>
                                    <option value="<?php echo htmlspecialchars($option); ?>"><?php echo htmlspecialchars($option); ?></option>
                                <?php endforeach; ?>
                            </select>
                        <?php elseif (smartenroll_input_type_for($fieldKey, $customFieldMap) === 'textarea'): ?>
                            <textarea name="<?php echo htmlspecialchars($fieldKey); ?>" placeholder="<?php echo htmlspecialchars(smartenroll_field_labelize($fieldKey, $customFieldMap)); ?>"></textarea>
                        <?php else: ?>
                            <input type="<?php echo htmlspecialchars(smartenroll_input_type_for($fieldKey, $customFieldMap)); ?>" name="<?php echo htmlspecialchars($fieldKey); ?>" placeholder="<?php echo htmlspecialchars(smartenroll_field_labelize($fieldKey, $customFieldMap)); ?>">
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </section>
    <!-- PARENT / GUARDIAN INFO -->
    <section class="form-section">
        <h2>C. PARENT / GUARDIAN INFORMATION</h2><br>

        <!-- FATHER -->
        <h2>Father's Information</h2>

        <div class="form-grid three">
            <div class="form-group">
                <label>Last Name</label>
                <input type="text" name="father_lname"placeholder="Last Name">
            </div>
            <div class="form-group">
                <label>First Name</label>
                <input type="text" name="father_fname"placeholder="First Name">
            </div>
            <div class="form-group">
                <label>Middle Name</label>
                <input type="text" name="father_mname"placeholder="Middle Name">
            </div>
        </div>

        <div class="form-grid one">
            <div class="form-group">    
                <label>Occupation</label>
                <input type="text" name="father_occ"placeholder="Occupation">
            </div>
        </div>

        <div class="form-grid one">
            <div class="form-group">
                <label>Contact Number</label>
                <input type="text" name="father_contact"placeholder="Contact Number">
            </div>
        </div>

        <?php if (!empty($customFieldsBySection['Father Information'])): ?>
            <div class="form-grid two">
                <?php foreach ($customFieldsBySection['Father Information'] as $field): ?>
                    <?php $fieldKey = (string)$field['field_key']; ?>
                    <div class="form-group">
                        <label><?php echo htmlspecialchars(smartenroll_field_labelize($fieldKey, $customFieldMap)); ?></label>
                        <?php if (smartenroll_input_type_for($fieldKey, $customFieldMap) === 'select'): ?>
                            <select name="<?php echo htmlspecialchars($fieldKey); ?>">
                                <option value="">Select</option>
                                <?php foreach (smartenroll_custom_field_options($field) as $option): ?>
                                    <option value="<?php echo htmlspecialchars($option); ?>"><?php echo htmlspecialchars($option); ?></option>
                                <?php endforeach; ?>
                            </select>
                        <?php elseif (smartenroll_input_type_for($fieldKey, $customFieldMap) === 'textarea'): ?>
                            <textarea name="<?php echo htmlspecialchars($fieldKey); ?>" placeholder="<?php echo htmlspecialchars(smartenroll_field_labelize($fieldKey, $customFieldMap)); ?>"></textarea>
                        <?php else: ?>
                            <input type="<?php echo htmlspecialchars(smartenroll_input_type_for($fieldKey, $customFieldMap)); ?>" name="<?php echo htmlspecialchars($fieldKey); ?>" placeholder="<?php echo htmlspecialchars(smartenroll_field_labelize($fieldKey, $customFieldMap)); ?>">
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <!-- MOTHER -->
        <h2>Mother's Information</h2>

        <div class="form-grid three">
            <div class="form-group">
                <label>Last Name</label>
                <input type="text" name="mother_lname"placeholder="Last Name">
            </div>
            <div class="form-group">
                <label>First Name</label>
                <input type="text" name="mother_fname"placeholder="First Name">
            </div>
            <div class="form-group">
                <label>Middle Name</label>
                <input type="text" name="mother_mname"placeholder="Middle Name">
            </div>
        </div>

        <div class="form-grid one">
            <div class="form-group">
                <label>Occupation</label>
                <input type="text" name="mother_occ"placeholder="Occupation">
            </div>
        </div>

        <div class="form-grid one">
            <div class="form-group">
                <label>Contact Number</label>
                <input type="text" name="mother_contact"placeholder="Contact Number">
            </div>
        </div>

        <div class="form-grid one">
            <div class="form-group">
                <label>Mother (Maiden Full Name)</label>
                <input type="text" name="mother_maiden"placeholder="Last Name, First Name, Middle Name">
            </div>
        </div>

        <?php if (!empty($customFieldsBySection['Mother Information'])): ?>
            <div class="form-grid two">
                <?php foreach ($customFieldsBySection['Mother Information'] as $field): ?>
                    <?php $fieldKey = (string)$field['field_key']; ?>
                    <div class="form-group">
                        <label><?php echo htmlspecialchars(smartenroll_field_labelize($fieldKey, $customFieldMap)); ?></label>
                        <?php if (smartenroll_input_type_for($fieldKey, $customFieldMap) === 'select'): ?>
                            <select name="<?php echo htmlspecialchars($fieldKey); ?>">
                                <option value="">Select</option>
                                <?php foreach (smartenroll_custom_field_options($field) as $option): ?>
                                    <option value="<?php echo htmlspecialchars($option); ?>"><?php echo htmlspecialchars($option); ?></option>
                                <?php endforeach; ?>
                            </select>
                        <?php elseif (smartenroll_input_type_for($fieldKey, $customFieldMap) === 'textarea'): ?>
                            <textarea name="<?php echo htmlspecialchars($fieldKey); ?>" placeholder="<?php echo htmlspecialchars(smartenroll_field_labelize($fieldKey, $customFieldMap)); ?>"></textarea>
                        <?php else: ?>
                            <input type="<?php echo htmlspecialchars(smartenroll_input_type_for($fieldKey, $customFieldMap)); ?>" name="<?php echo htmlspecialchars($fieldKey); ?>" placeholder="<?php echo htmlspecialchars(smartenroll_field_labelize($fieldKey, $customFieldMap)); ?>">
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <!-- GUARDIAN -->
        <h2>Guardian's Information</h2>

        <div class="form-grid one">
            <div class="form-group">
                <label>Guardian Type</label>
                <div style="display:flex; gap:30px;">
                    <label><input type="radio" name="guardian_type" value="other"> Other</label>

                    <label><input type="radio" name="guardian_type" value="mother"> Mother</label>
                    <label><input type="radio" name="guardian_type" value="father"> Father</label>
                    
                </div>
            </div>
        </div>

        <div class="form-grid three">
            <div class="form-group">
                <label>Last Name</label>
                <input type="text" name="guardian_lname" readonly>
            </div>
            <div class="form-group">
                <label>First Name</label>
                <input type="text" name="guardian_fname" readonly>
            </div>
            <div class="form-group">
                <label>Middle Name</label>
                <input type="text" name="guardian_mname" readonly>
            </div>
        </div>

        <div class="form-grid one">
            <div class="form-group">
                <label>Occupation</label>
                <input type="text" name="guardian_occ" readonly>
            </div>
        </div>

        <div class="form-grid one">
            <div class="form-group">
                <label>Contact Number</label>
                <input type="text" name="guardian_contact" readonly>
            </div>
        </div>

        <?php if (!empty($customFieldsBySection['Guardian Information'])): ?>
            <div class="form-grid two">
                <?php foreach ($customFieldsBySection['Guardian Information'] as $field): ?>
                    <?php $fieldKey = (string)$field['field_key']; ?>
                    <div class="form-group">
                        <label><?php echo htmlspecialchars(smartenroll_field_labelize($fieldKey, $customFieldMap)); ?></label>
                        <?php if (smartenroll_input_type_for($fieldKey, $customFieldMap) === 'select'): ?>
                            <select name="<?php echo htmlspecialchars($fieldKey); ?>" data-guardian-field="<?php echo htmlspecialchars($fieldKey); ?>">
                                <option value="">Select</option>
                                <?php foreach (smartenroll_custom_field_options($field) as $option): ?>
                                    <option value="<?php echo htmlspecialchars($option); ?>"><?php echo htmlspecialchars($option); ?></option>
                                <?php endforeach; ?>
                            </select>
                        <?php elseif (smartenroll_input_type_for($fieldKey, $customFieldMap) === 'textarea'): ?>
                            <textarea name="<?php echo htmlspecialchars($fieldKey); ?>" placeholder="<?php echo htmlspecialchars(smartenroll_field_labelize($fieldKey, $customFieldMap)); ?>" data-guardian-field="<?php echo htmlspecialchars($fieldKey); ?>"></textarea>
                        <?php else: ?>
                            <input type="<?php echo htmlspecialchars(smartenroll_input_type_for($fieldKey, $customFieldMap)); ?>" name="<?php echo htmlspecialchars($fieldKey); ?>" placeholder="<?php echo htmlspecialchars(smartenroll_field_labelize($fieldKey, $customFieldMap)); ?>" data-guardian-field="<?php echo htmlspecialchars($fieldKey); ?>">
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </section>
    <!-- LEARNERS WITH SPECIAL EDUCATION NEEDS -->
    <section class="form-section">
        <h2>D. Learners with Special Education Needs</h2>

        <!-- D1 -->
        <div class="form-grid one">
            <div class="form-group">
                <label>
                    D1. Does the learner have special education needs?
                    <br>
                    <small>(e.g. physical, mental, social disability, giftedness, among others)</small>
                </label>
                <input type="text" name="special_needs" placeholder="Specify if any">
            </div>
        </div>

        <!-- D2 -->
            <div class="form-grid one">
                <div class="form-group">
                    <label>D2. Does your child take any medication?</label>
                    <div style="display:flex; gap:30px; margin-top:10px;">
                        <label><input type="radio" name="medication" value="yes"> Yes</label>
                        <label><input type="radio" name="medication" value="no" checked> No</label>
                    </div>
                </div>
            </div>

        <!-- D3 -->
        <div class="form-grid one">
            <div class="form-group">
                <label>D3. What medication?</label>
                <input
                    type="text"
                    id="medication_details"
                    name="medication_details"
                    placeholder="Specify medication"
                    disabled
                >
            </div>
        </div>

        <?php if (!empty($customFieldsBySection['Special Education Needs'])): ?>
            <div class="form-grid two">
                <?php foreach ($customFieldsBySection['Special Education Needs'] as $field): ?>
                    <?php $fieldKey = (string)$field['field_key']; ?>
                    <div class="form-group">
                        <label><?php echo htmlspecialchars(smartenroll_field_labelize($fieldKey, $customFieldMap)); ?></label>
                        <?php if (smartenroll_input_type_for($fieldKey, $customFieldMap) === 'select'): ?>
                            <select name="<?php echo htmlspecialchars($fieldKey); ?>">
                                <option value="">Select</option>
                                <?php foreach (smartenroll_custom_field_options($field) as $option): ?>
                                    <option value="<?php echo htmlspecialchars($option); ?>"><?php echo htmlspecialchars($option); ?></option>
                                <?php endforeach; ?>
                            </select>
                        <?php elseif (smartenroll_input_type_for($fieldKey, $customFieldMap) === 'textarea'): ?>
                            <textarea name="<?php echo htmlspecialchars($fieldKey); ?>" placeholder="<?php echo htmlspecialchars(smartenroll_field_labelize($fieldKey, $customFieldMap)); ?>"></textarea>
                        <?php else: ?>
                            <input type="<?php echo htmlspecialchars(smartenroll_input_type_for($fieldKey, $customFieldMap)); ?>" name="<?php echo htmlspecialchars($fieldKey); ?>" placeholder="<?php echo htmlspecialchars(smartenroll_field_labelize($fieldKey, $customFieldMap)); ?>">
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </section>
    <!-- IN CASE OF EMERGENCY -->
    <section class="form-section">
        <h2>E. In Case of Emergency (Call Order of Priority)</h2>

        <!-- 1st Priority -->
        <div class="form-grid three emergency-contact-block" data-emergency-block="1">
            <div class="form-group">
                <label>1st: Parent/Guardian Name</label>
                <input type="text" name="emergency1_name" placeholder="Full Name">
            </div>

            <div class="form-group">
                <label>Contact No.</label>
                <input type="tel" name="emergency1_contact" placeholder="09XXXXXXXXX">
            </div>

            <div class="form-group">
                <label>Relationship</label>
                <input type="text" name="emergency1_relationship" placeholder="e.g. Mother, Father, Guardian">
            </div>
        </div>

        <!-- 2nd Priority -->
        <div class="form-grid three emergency-contact-block emergency-contact-hidden" data-emergency-block="2">
            <div class="form-group">
                <label>2nd: Parent/Guardian Name</label>
                <input type="text" name="emergency2_name" placeholder="Full Name" disabled>
            </div>

            <div class="form-group">
                <label>Contact No.</label>
                <input type="tel" name="emergency2_contact" placeholder="09XXXXXXXXX" disabled>
            </div>

            <div class="form-group">
                <label>Relationship</label>
                <input type="text" name="emergency2_relationship"placeholder="e.g. Aunt, Uncle, Guardian" disabled>
            </div>
        </div>

        <!-- 3rd Priority -->
        <div class="form-grid three emergency-contact-block emergency-contact-hidden" data-emergency-block="3">
            <div class="form-group">
                <label>3rd: Parent/Guardian Name</label>
                <input type="text" name="emergency3_name" placeholder="Full Name" disabled>
            </div>

            <div class="form-group">
                <label>Contact No.</label>
                <input type="tel" name="emergency3_contact" placeholder="09XXXXXXXXX" disabled>
            </div>

            <div class="form-group">
                <label>Relationship</label>
                <input type="text" name="emergency3_relationship" placeholder="e.g. Relative, Caregiver" disabled>
            </div>
        </div>

        <div class="emergency-contact-actions">
            <button type="button" class="emergency-add-btn" id="addEmergencyContactBtn">+ Add Emergency Contact</button>
        </div>

        <?php if (!empty($customFieldsBySection['Emergency Contacts'])): ?>
            <div class="form-grid two">
                <?php foreach ($customFieldsBySection['Emergency Contacts'] as $field): ?>
                    <?php $fieldKey = (string)$field['field_key']; ?>
                    <div class="form-group">
                        <label><?php echo htmlspecialchars(smartenroll_field_labelize($fieldKey, $customFieldMap)); ?></label>
                        <?php if (smartenroll_input_type_for($fieldKey, $customFieldMap) === 'select'): ?>
                            <select name="<?php echo htmlspecialchars($fieldKey); ?>">
                                <option value="">Select</option>
                                <?php foreach (smartenroll_custom_field_options($field) as $option): ?>
                                    <option value="<?php echo htmlspecialchars($option); ?>"><?php echo htmlspecialchars($option); ?></option>
                                <?php endforeach; ?>
                            </select>
                        <?php elseif (smartenroll_input_type_for($fieldKey, $customFieldMap) === 'textarea'): ?>
                            <textarea name="<?php echo htmlspecialchars($fieldKey); ?>" placeholder="<?php echo htmlspecialchars(smartenroll_field_labelize($fieldKey, $customFieldMap)); ?>"></textarea>
                        <?php else: ?>
                            <input type="<?php echo htmlspecialchars(smartenroll_input_type_for($fieldKey, $customFieldMap)); ?>" name="<?php echo htmlspecialchars($fieldKey); ?>" placeholder="<?php echo htmlspecialchars(smartenroll_field_labelize($fieldKey, $customFieldMap)); ?>">
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </section>
    <!-- SUBMIT BUTTON -->
    <div class="form-submit-area">
        <button type="button" id="submitBtn" class="submit-btn">
            Submit Enrollment
        </button>
    </div>
</form>
    </main>
    <!-- SUMMARY MODAL -->
    <div class="modal-overlay" id="summaryModal">
        <div class="modal-box">
            <h2>Confirm Enrollment Details</h2>

            <div id="summaryContent"></div>

            <div class="modal-actions">
            <button type="button" id="confirmSubmit" class="confirm-btn">Confirm</button>

    
                <button id="cancelSubmit" class="cancel-btn">Cancel</button>
            </div>
        </div>
    </div>



<!-- SUCCESS POPUP -->
<div id="successPopup" class="popup-overlay">

  <div class="popup-box">

    <!-- LOGO → CHECK MORPH ICON -->
    <div class="popup-icon success-icon" id="successIcon">
        <img src="assets/logo.png" id="successLogo" alt="Logo">
        <i class="fas fa-check" id="successCheck"></i>
    </div>

    <h2>Enrollment Successful</h2>

    <p>The student has been successfully enrolled.</p>

    <button class="popup-btn" id="closeSuccess">OK</button>

  </div>

</div>

<!-- VALIDATION POPUP -->
<div id="validationPopup" class="popup-overlay">

  <div class="popup-box">

    <!-- LOGO → X MORPH ICON -->
    <div class="popup-icon" id="popupIcon">
        <img src="assets/logo.png" id="popupLogo" alt="Logo">
        <i class="fas fa-times" id="popupX"></i>
    </div>

    <h2>Incomplete Form</h2>

    <p>Please complete all required fields before submitting.</p>

    <button class="popup-btn" id="okValidation">OK</button>

  </div>

</div>

    <script src="js/enroll.js"></script>

    </body>
    </html>
