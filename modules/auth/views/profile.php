<?php
$profileData = $profile ?? [];
$role = (string) ($user['role'] ?? 'general');
$isGeneral = in_array(auth_normalize_role($role), ['general'], true);
$isVolunteer = auth_normalize_role($role) === 'volunteer';
$isNgo = auth_normalize_role($role) === 'ngo';

$fullName = $profileData['full_name'] ?? $profileData['name'] ?? $profileData['contact_person'] ?? $profileData['organization_name'] ?? $profileData['org_name'] ?? $user['name'] ?? '';
$contactNo = $profileData['contact_no'] ?? $profileData['contact_number'] ?? $profileData['contact'] ?? $profileData['telephone'] ?? '';
$district = $profileData['district'] ?? '';
$gnDivision = $profileData['gn_division'] ?? '';
$orgName = $profileData['organization_name'] ?? $profileData['org_name'] ?? '';
$registrationNo = $profileData['registration_no'] ?? $profileData['registration_number'] ?? $profileData['reg_no'] ?? '';
$years = $profileData['years_of_operation'] ?? $profileData['years'] ?? '';
$telephone = $profileData['telephone'] ?? '';
$address = $profileData['address'] ?? '';
$age = $profileData['age'] ?? '';
$gender = $profileData['gender'] ?? '';
$smsAlert = !empty($profileData['sms_alert']);
?>

<div class="card">
    <div class="card-header">
        <h2>Profile Settings</h2>
    </div>
    <div class="card-body">
        <?php if ($error = get_flash('error')): ?>
            <div class="alert alert-error"><?= e($error) ?></div>
        <?php endif; ?>
        <?php if ($info = get_flash('info')): ?>
            <div class="alert alert-info"><?= e($info) ?></div>
        <?php endif; ?>

        <form method="POST" action="/profile">
            <?= csrf_field() ?>

            <div class="form-group">
                <label for="username">Username</label>
                <input type="text" id="username" name="username" value="<?= e($user['username'] ?? '') ?>">
            </div>

            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" value="<?= e($user['email'] ?? '') ?>">
            </div>

            <div class="form-group">
                <label for="full_name">Name</label>
                <input type="text" id="full_name" name="full_name" value="<?= e((string) $fullName) ?>">
            </div>

            <div class="form-group">
                <label for="contact_no">Contact No</label>
                <input type="text" id="contact_no" name="contact_no" value="<?= e((string) $contactNo) ?>">
            </div>

            <div class="form-group">
                <label for="district">District</label>
                <input type="text" id="district" name="district" value="<?= e((string) $district) ?>">
            </div>

            <div class="form-group">
                <label for="gn_division">Grama Niladhari Division</label>
                <input type="text" id="gn_division" name="gn_division" value="<?= e((string) $gnDivision) ?>">
            </div>

            <?php if ($isVolunteer): ?>
                <div class="form-group">
                    <label for="age">Age</label>
                    <input type="number" id="age" name="age" min="16" value="<?= e((string) $age) ?>">
                </div>

                <div class="form-group">
                    <label for="gender">Gender</label>
                    <input type="text" id="gender" name="gender" value="<?= e((string) $gender) ?>">
                </div>
            <?php endif; ?>

            <?php if ($isNgo): ?>
                <div class="form-group">
                    <label for="org_name">Organization Name</label>
                    <input type="text" id="org_name" name="org_name" value="<?= e((string) $orgName) ?>">
                </div>

                <div class="form-group">
                    <label for="registration_no">Registration No.</label>
                    <input type="text" id="registration_no" name="registration_no" value="<?= e((string) $registrationNo) ?>">
                </div>

                <div class="form-group">
                    <label for="years_of_operation">Years of Operation</label>
                    <input type="number" id="years_of_operation" name="years_of_operation" min="0" value="<?= e((string) $years) ?>">
                </div>

                <div class="form-group">
                    <label for="telephone">Telephone</label>
                    <input type="text" id="telephone" name="telephone" value="<?= e((string) $telephone) ?>">
                </div>

                <div class="form-group">
                    <label for="address">Address</label>
                    <input type="text" id="address" name="address" value="<?= e((string) $address) ?>">
                </div>
            <?php endif; ?>

            <div class="form-group">
                <label for="password">New Password (optional)</label>
                <input type="password" id="password" name="password" minlength="8" autocomplete="new-password">
            </div>

            <div class="form-group">
                <label for="password_confirmation">Confirm New Password</label>
                <input type="password" id="password_confirmation" name="password_confirmation" minlength="8" autocomplete="new-password">
            </div>

            <div class="form-actions">
                <button type="submit" class="btn btn-primary">Save Changes</button>
            </div>
        </form>

        <?php if ($isGeneral): ?>
            <hr style="margin: 26px 0; border: 0; border-top: 1px solid var(--border-color);">
            <h3 style="margin-bottom: 10px;">SMS Alerts</h3>
            <form method="POST" action="/profile/sms-alert">
                <?= csrf_field() ?>
                <label style="display: flex; gap: 8px; align-items: center; color: var(--text-secondary);">
                    <input type="checkbox" name="sms_alert" value="1" <?= $smsAlert ? 'checked' : '' ?>>
                    <span>Receive SMS alerts for severe weather and disaster warnings.</span>
                </label>
                <div class="form-actions" style="margin-top: 12px;">
                    <button type="submit" class="btn btn-outline">Update SMS Preference</button>
                </div>
            </form>
        <?php endif; ?>
    </div>
</div>
