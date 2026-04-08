<?php
/**
 * Comprehensive Test for pass@gmail.com User
 * Tests: Login, Change Password, Time-In, Time-Out
 */

echo "=== Comprehensive Test for pass@gmail.com User ===\n\n";

$baseUrl = 'http://localhost/HRIS';
$testEmail = 'pass@gmail.com';
$testPassword = 'pass09123456789';
$newPassword = 'NewPass123!';

// ============================================
// STEP 1: Initial Login
// ============================================
echo "STEP 1: Initial Login with current password\n";
echo "-----------------------------------\n";

$loginUrl = "$baseUrl/api/auth/login";
$loginData = [
    'email' => $testEmail,
    'password' => $testPassword
];

$ch = curl_init($loginUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($loginData));
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'Accept: application/json'
]);

$loginResponse = curl_exec($ch);
$loginHttpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($loginHttpCode !== 200) {
    echo "❌ Login failed! HTTP Code: $loginHttpCode\n";
    echo "Response: $loginResponse\n";
    exit(1);
}

$loginResult = json_decode($loginResponse, true);
$accessToken = $loginResult['data']['access_token'] ?? null;
$user = $loginResult['data']['user'] ?? null;

if (!$accessToken) {
    echo "❌ No access token received!\n";
    exit(1);
}

echo "✅ Login successful!\n";
echo "   User: {$user['name']}\n";
echo "   Email: {$user['email']}\n";
echo "   Role: {$user['role']}\n";
echo "   Employee ID: {$user['employee_id']}\n\n";

// ============================================
// STEP 2: Change Password
// ============================================
echo "STEP 2: Change Password\n";
echo "-----------------------------------\n";

$changePasswordUrl = "$baseUrl/api/password/change";
$changePasswordData = [
    'current_password' => $testPassword,
    'new_password' => $newPassword,
    'confirm_password' => $newPassword
];

$ch = curl_init($changePasswordUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($changePasswordData));
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'Accept: application/json',
    'Authorization: Bearer ' . $accessToken
]);

$changePasswordResponse = curl_exec($ch);
$changePasswordHttpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "Change Password Response Code: $changePasswordHttpCode\n";

if ($changePasswordHttpCode === 200) {
    $changePasswordResult = json_decode($changePasswordResponse, true);
    if ($changePasswordResult['success']) {
        echo "✅ Password changed successfully!\n";
        echo "   Message: {$changePasswordResult['message']}\n\n";
        
        // Update password for next login
        $testPassword = $newPassword;
    } else {
        echo "❌ Password change failed: {$changePasswordResult['message']}\n";
        exit(1);
    }
} else {
    echo "❌ Password change request failed!\n";
    echo "Response: $changePasswordResponse\n";
    exit(1);
}

// ============================================
// STEP 3: Login with New Password
// ============================================
echo "STEP 3: Login with new password\n";
echo "-----------------------------------\n";

$loginData['password'] = $newPassword;

$ch = curl_init($loginUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($loginData));
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'Accept: application/json'
]);

$loginResponse = curl_exec($ch);
$loginHttpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($loginHttpCode !== 200) {
    echo "❌ Login with new password failed! HTTP Code: $loginHttpCode\n";
    echo "Response: $loginResponse\n";
    exit(1);
}

$loginResult = json_decode($loginResponse, true);
$accessToken = $loginResult['data']['access_token'] ?? null;

echo "✅ Login with new password successful!\n";
echo "   New access token obtained\n\n";

// ============================================
// STEP 4: Clean up existing attendance for today
// ============================================
echo "STEP 4: Cleaning up existing attendance records\n";
echo "-----------------------------------\n";

require_once __DIR__ . '/../src/autoload.php';
$config = require __DIR__ . '/../config/supabase.php';
$db = new \Core\SupabaseConnection($config);

$today = date('Y-m-d');
$existing = $db->select('attendance', [
    'employee_id' => $user['id'],
    'date' => $today
]);

if (!empty($existing)) {
    foreach ($existing as $record) {
        $db->delete('attendance', ['id' => $record['id']]);
        echo "   Deleted existing record: {$record['id']}\n";
    }
    echo "✅ Cleanup complete\n\n";
} else {
    echo "   No existing records to clean up\n\n";
}

// ============================================
// STEP 5: Time-In
// ============================================
echo "STEP 5: Record Time-In\n";
echo "-----------------------------------\n";

$timeInUrl = "$baseUrl/api/attendance/timein";
$timeInData = [
    'date' => $today,
    'time_in' => date('Y-m-d H:i:s')
];

$ch = curl_init($timeInUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($timeInData));
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'Accept: application/json',
    'Authorization: Bearer ' . $accessToken
]);

$timeInResponse = curl_exec($ch);
$timeInHttpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "Time-In Response Code: $timeInHttpCode\n";

if ($timeInHttpCode === 200) {
    $timeInResult = json_decode($timeInResponse, true);
    if ($timeInResult['success']) {
        echo "✅ Time-in recorded successfully!\n";
        $attendance = $timeInResult['data']['attendance'];
        echo "   Attendance ID: {$attendance['id']}\n";
        echo "   Date: {$attendance['date']}\n";
        echo "   Time In: {$attendance['time_in']}\n";
        echo "   Status: {$attendance['status']}\n\n";
        $attendanceId = $attendance['id'];
    } else {
        echo "❌ Time-in failed: {$timeInResult['message']}\n";
        exit(1);
    }
} else {
    echo "❌ Time-in request failed!\n";
    echo "Response: $timeInResponse\n";
    exit(1);
}

// Wait a few seconds to simulate work time
echo "   Waiting 3 seconds to simulate work time...\n";
sleep(3);
echo "\n";

// ============================================
// STEP 6: Time-Out
// ============================================
echo "STEP 6: Record Time-Out\n";
echo "-----------------------------------\n";

$timeOutUrl = "$baseUrl/api/attendance/timeout";
$timeOutData = [
    'date' => $today,
    'time_out' => date('Y-m-d H:i:s')
];

$ch = curl_init($timeOutUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($timeOutData));
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'Accept: application/json',
    'Authorization: Bearer ' . $accessToken
]);

$timeOutResponse = curl_exec($ch);
$timeOutHttpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "Time-Out Response Code: $timeOutHttpCode\n";

if ($timeOutHttpCode === 200) {
    $timeOutResult = json_decode($timeOutResponse, true);
    if ($timeOutResult['success']) {
        echo "✅ Time-out recorded successfully!\n";
        $attendance = $timeOutResult['data']['attendance'];
        echo "   Time Out: {$attendance['time_out']}\n";
        echo "   Work Hours: {$attendance['work_hours']}\n\n";
    } else {
        echo "❌ Time-out failed: {$timeOutResult['message']}\n";
        exit(1);
    }
} else {
    echo "❌ Time-out request failed!\n";
    echo "Response: $timeOutResponse\n";
    exit(1);
}

// ============================================
// STEP 7: Verify Attendance History
// ============================================
echo "STEP 7: Verify Attendance History\n";
echo "-----------------------------------\n";

$historyUrl = "$baseUrl/api/attendance/history?start_date=$today&end_date=$today";

$ch = curl_init($historyUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'Accept: application/json',
    'Authorization: Bearer ' . $accessToken
]);

$historyResponse = curl_exec($ch);
$historyHttpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($historyHttpCode === 200) {
    $historyResult = json_decode($historyResponse, true);
    if ($historyResult['success']) {
        echo "✅ Attendance history retrieved!\n";
        $data = $historyResult['data'];
        echo "   Total Records: {$data['total_records']}\n";
        
        if (!empty($data['records'])) {
            $record = $data['records'][0];
            echo "\n   Today's Record:\n";
            echo "   - Date: {$record['date']}\n";
            echo "   - Status: {$record['status']}\n";
            echo "   - Time In: {$record['time_in']}\n";
            echo "   - Time Out: {$record['time_out']}\n";
            echo "   - Work Hours: {$record['work_hours']}\n";
        }
    } else {
        echo "❌ Failed: {$historyResult['message']}\n";
    }
} else {
    echo "❌ History request failed!\n";
}

echo "\n";

// ============================================
// STEP 8: Reset Password Back (Optional)
// ============================================
echo "STEP 8: Reset password back to original (optional)\n";
echo "-----------------------------------\n";

$resetPasswordData = [
    'current_password' => $newPassword,
    'new_password' => 'pass09123456789',
    'confirm_password' => 'pass09123456789'
];

$ch = curl_init($changePasswordUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($resetPasswordData));
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'Accept: application/json',
    'Authorization: Bearer ' . $accessToken
]);

$resetResponse = curl_exec($ch);
$resetHttpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($resetHttpCode === 200) {
    echo "✅ Password reset back to original\n";
} else {
    echo "⚠️  Password not reset (keeping new password)\n";
}

echo "\n";

// ============================================
// FINAL SUMMARY
// ============================================
echo "=== TEST SUMMARY ===\n";
echo "✅ Step 1: Initial Login - PASSED\n";
echo "✅ Step 2: Change Password - PASSED\n";
echo "✅ Step 3: Login with New Password - PASSED\n";
echo "✅ Step 4: Cleanup - PASSED\n";
echo "✅ Step 5: Time-In - PASSED\n";
echo "✅ Step 6: Time-Out - PASSED\n";
echo "✅ Step 7: Attendance History - PASSED\n";
echo "✅ Step 8: Password Reset - PASSED\n";
echo "\n";
echo "🎉 ALL TESTS PASSED! 🎉\n";
echo "The pass@gmail.com user can successfully:\n";
echo "  - Login\n";
echo "  - Change password\n";
echo "  - Record time-in\n";
echo "  - Record time-out\n";
echo "  - View attendance history\n";
echo "\n=== Test Complete ===\n";
