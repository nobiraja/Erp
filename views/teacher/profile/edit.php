<?php
/**
 * Teacher Profile Edit View
 * Form for editing teacher profile information
 */

if (!isset($teacher)) {
    echo '<div class="alert alert-danger">Teacher data not available</div>';
    return;
}
?>

<div class="row">
    <div class="col-md-8">
        <form id="updateProfileForm" enctype="multipart/form-data">
            <div class="row">
                <!-- Personal Information -->
                <div class="col-md-6">
                    <h5 class="mb-3">Personal Information</h5>

                    <div class="mb-3">
                        <label for="first_name" class="form-label">First Name *</label>
                        <input type="text" class="form-control" id="first_name" name="first_name"
                               value="<?= htmlspecialchars($teacher->first_name ?? '') ?>" required>
                    </div>

                    <div class="mb-3">
                        <label for="middle_name" class="form-label">Middle Name</label>
                        <input type="text" class="form-control" id="middle_name" name="middle_name"
                               value="<?= htmlspecialchars($teacher->middle_name ?? '') ?>">
                    </div>

                    <div class="mb-3">
                        <label for="last_name" class="form-label">Last Name *</label>
                        <input type="text" class="form-control" id="last_name" name="last_name"
                               value="<?= htmlspecialchars($teacher->last_name ?? '') ?>" required>
                    </div>

                    <div class="mb-3">
                        <label for="dob" class="form-label">Date of Birth</label>
                        <input type="date" class="form-control" id="dob" name="dob"
                               value="<?= $teacher->dob ?? '' ?>">
                    </div>

                    <div class="mb-3">
                        <label for="gender" class="form-label">Gender</label>
                        <select class="form-select" id="gender" name="gender">
                            <option value="">Select Gender</option>
                            <option value="male" <?= ($teacher->gender ?? '') === 'male' ? 'selected' : '' ?>>Male</option>
                            <option value="female" <?= ($teacher->gender ?? '') === 'female' ? 'selected' : '' ?>>Female</option>
                            <option value="other" <?= ($teacher->gender ?? '') === 'other' ? 'selected' : '' ?>>Other</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="marital_status" class="form-label">Marital Status</label>
                        <select class="form-select" id="marital_status" name="marital_status">
                            <option value="">Select Status</option>
                            <option value="single" <?= ($teacher->marital_status ?? '') === 'single' ? 'selected' : '' ?>>Single</option>
                            <option value="married" <?= ($teacher->marital_status ?? '') === 'married' ? 'selected' : '' ?>>Married</option>
                            <option value="divorced" <?= ($teacher->marital_status ?? '') === 'divorced' ? 'selected' : '' ?>>Divorced</option>
                            <option value="widowed" <?= ($teacher->marital_status ?? '') === 'widowed' ? 'selected' : '' ?>>Widowed</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="blood_group" class="form-label">Blood Group</label>
                        <select class="form-select" id="blood_group" name="blood_group">
                            <option value="">Select Blood Group</option>
                            <option value="A+" <?= ($teacher->blood_group ?? '') === 'A+' ? 'selected' : '' ?>>A+</option>
                            <option value="A-" <?= ($teacher->blood_group ?? '') === 'A-' ? 'selected' : '' ?>>A-</option>
                            <option value="B+" <?= ($teacher->blood_group ?? '') === 'B+' ? 'selected' : '' ?>>B+</option>
                            <option value="B-" <?= ($teacher->blood_group ?? '') === 'B-' ? 'selected' : '' ?>>B-</option>
                            <option value="AB+" <?= ($teacher->blood_group ?? '') === 'AB+' ? 'selected' : '' ?>>AB+</option>
                            <option value="AB-" <?= ($teacher->blood_group ?? '') === 'AB-' ? 'selected' : '' ?>>AB-</option>
                            <option value="O+" <?= ($teacher->blood_group ?? '') === 'O+' ? 'selected' : '' ?>>O+</option>
                            <option value="O-" <?= ($teacher->blood_group ?? '') === 'O-' ? 'selected' : '' ?>>O-</option>
                        </select>
                    </div>
                </div>

                <!-- Professional Information -->
                <div class="col-md-6">
                    <h5 class="mb-3">Professional Information</h5>

                    <div class="mb-3">
                        <label for="qualification" class="form-label">Qualification</label>
                        <input type="text" class="form-control" id="qualification" name="qualification"
                               value="<?= htmlspecialchars($teacher->qualification ?? '') ?>">
                    </div>

                    <div class="mb-3">
                        <label for="specialization" class="form-label">Specialization/Subjects</label>
                        <input type="text" class="form-control" id="specialization" name="specialization"
                               value="<?= htmlspecialchars($teacher->specialization ?? '') ?>">
                    </div>

                    <div class="mb-3">
                        <label for="designation" class="form-label">Designation</label>
                        <input type="text" class="form-control" id="designation" name="designation"
                               value="<?= htmlspecialchars($teacher->designation ?? '') ?>">
                    </div>

                    <div class="mb-3">
                        <label for="department" class="form-label">Department</label>
                        <input type="text" class="form-control" id="department" name="department"
                               value="<?= htmlspecialchars($teacher->department ?? '') ?>">
                    </div>

                    <div class="mb-3">
                        <label for="date_of_joining" class="form-label">Date of Joining</label>
                        <input type="date" class="form-control" id="date_of_joining" name="date_of_joining"
                               value="<?= $teacher->date_of_joining ?? '' ?>">
                    </div>

                    <div class="mb-3">
                        <label for="experience_years" class="form-label">Experience (Years)</label>
                        <input type="number" class="form-control" id="experience_years" name="experience_years"
                               value="<?= $teacher->experience_years ?? '' ?>" min="0" step="0.5">
                    </div>
                </div>
            </div>

            <hr>

            <!-- Contact Information -->
            <h5 class="mb-3">Contact Information</h5>
            <div class="row">
                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="mobile" class="form-label">Mobile Number</label>
                        <input type="tel" class="form-control" id="mobile" name="mobile"
                               value="<?= htmlspecialchars($teacher->mobile ?? '') ?>">
                    </div>

                    <div class="mb-3">
                        <label for="email" class="form-label">Email Address</label>
                        <input type="email" class="form-control" id="email" name="email"
                               value="<?= htmlspecialchars($teacher->email ?? '') ?>">
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="permanent_address" class="form-label">Permanent Address</label>
                        <textarea class="form-control" id="permanent_address" name="permanent_address" rows="3"><?= htmlspecialchars($teacher->permanent_address ?? '') ?></textarea>
                    </div>

                    <div class="mb-3">
                        <label for="temporary_address" class="form-label">Temporary Address</label>
                        <textarea class="form-control" id="temporary_address" name="temporary_address" rows="3"><?= htmlspecialchars($teacher->temporary_address ?? '') ?></textarea>
                    </div>
                </div>
            </div>

            <hr>

            <!-- Identification & Medical -->
            <h5 class="mb-3">Identification & Medical Information</h5>
            <div class="row">
                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="aadhar" class="form-label">Aadhar Number</label>
                        <input type="text" class="form-control" id="aadhar" name="aadhar"
                               value="<?= htmlspecialchars($teacher->aadhar ?? '') ?>" pattern="[0-9]{12}">
                    </div>

                    <div class="mb-3">
                        <label for="pan" class="form-label">PAN Number</label>
                        <input type="text" class="form-control" id="pan" name="pan"
                               value="<?= htmlspecialchars($teacher->pan ?? '') ?>" pattern="[A-Z]{5}[0-9]{4}[A-Z]{1}">
                    </div>

                    <div class="mb-3">
                        <label for="samagra_id" class="form-label">Samagra ID</label>
                        <input type="text" class="form-control" id="samagra_id" name="samagra_id"
                               value="<?= htmlspecialchars($teacher->samagra_id ?? '') ?>">
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="medical_conditions" class="form-label">Medical Conditions</label>
                        <textarea class="form-control" id="medical_conditions" name="medical_conditions" rows="3"
                                  placeholder="List any medical conditions or allergies"><?= htmlspecialchars($teacher->medical_conditions ?? '') ?></textarea>
                    </div>

                    <div class="mb-3">
                        <label for="photo" class="form-label">Profile Photo</label>
                        <input type="file" class="form-control" id="photo" name="photo" accept="image/*">
                        <div class="form-text">Accepted formats: JPG, PNG, GIF. Max size: 2MB</div>
                        <?php if ($teacher->photo_path): ?>
                            <div class="mt-2">
                                <img src="/uploads/<?= $teacher->photo_path ?>" alt="Current Photo"
                                     class="img-thumbnail" style="width: 100px; height: 100px; object-fit: cover;">
                                <small class="text-muted d-block">Current photo</small>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </form>
    </div>

    <div class="col-md-4">
        <div class="card">
            <div class="card-header">
                <h6 class="mb-0">Update Instructions</h6>
            </div>
            <div class="card-body">
                <ul class="list-unstyled">
                    <li class="mb-2"><i class="fas fa-check-circle text-success"></i> Fields marked with * are required</li>
                    <li class="mb-2"><i class="fas fa-info-circle text-info"></i> Changes will be saved immediately</li>
                    <li class="mb-2"><i class="fas fa-camera text-primary"></i> Upload a new photo to update your profile picture</li>
                    <li class="mb-2"><i class="fas fa-shield-alt text-warning"></i> Sensitive information is encrypted</li>
                    <li class="mb-0"><i class="fas fa-history text-secondary"></i> All changes are logged for security</li>
                </ul>
            </div>
        </div>

        <div class="card mt-3">
            <div class="card-header">
                <h6 class="mb-0">Profile Completion</h6>
            </div>
            <div class="card-body">
                <div class="progress mb-2">
                    <div class="progress-bar" role="progressbar" id="completionProgress" style="width: 0%"></div>
                </div>
                <small class="text-muted" id="completionText">Calculating...</small>
            </div>
        </div>
    </div>
</div>

<script>
// Calculate profile completion on load
$(document).ready(function() {
    calculateProfileCompletion();
});

// Calculate profile completion percentage
function calculateProfileCompletion() {
    const requiredFields = ['first_name', 'last_name', 'email'];
    const importantFields = ['mobile', 'permanent_address', 'qualification', 'designation', 'department'];
    const allFields = [...requiredFields, ...importantFields];

    let completed = 0;
    allFields.forEach(field => {
        const value = $('#' + field).val();
        if (value && value.trim() !== '') {
            completed++;
        }
    });

    const percentage = Math.round((completed / allFields.length) * 100);
    $('#completionProgress').css('width', percentage + '%');
    $('#completionText').text(percentage + '% complete');
}

// Handle form submission
$('#updateProfileForm').on('submit', function(e) {
    e.preventDefault();

    const formData = new FormData(this);

    // Show loading state
    const submitBtn = $(this).find('button[type="submit"]');
    const originalText = submitBtn.html();
    submitBtn.html('<i class="fas fa-spinner fa-spin"></i> Saving...').prop('disabled', true);

    $.ajax({
        url: '/teacher/profile/update',
        method: 'POST',
        data: formData,
        processData: false,
        contentType: false,
        success: function(response) {
            if (response.success) {
                // Close modal and refresh profile data
                $('#editProfileModal').modal('hide');
                location.reload();
            } else {
                alert(response.message || 'Failed to update profile');
            }
        },
        error: function() {
            alert('An error occurred while updating profile');
        },
        complete: function() {
            // Reset button state
            submitBtn.html(originalText).prop('disabled', false);
        }
    });
});

// Update completion on field changes
$('#updateProfileForm input, #updateProfileForm textarea, #updateProfileForm select').on('input change', function() {
    calculateProfileCompletion();
});
</script>