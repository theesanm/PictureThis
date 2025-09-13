<?php
/**
 * Timezone Test and Configuration Script
 * Helps users test timezone functionality and set preferences
 */
require_once __DIR__ . '/src/lib/timezone.php';

echo "<h1>Timezone Configuration Test</h1>";

// Test current timezone settings
echo "<h2>Current Server Settings</h2>";
echo "<p><strong>PHP Timezone:</strong> " . date_default_timezone_get() . "</p>";
echo "<p><strong>Current UTC Time:</strong> " . get_utc_now_string() . "</p>";
echo "<p><strong>Server Time:</strong> " . date('Y-m-d H:i:s T') . "</p>";

// Test timezone conversion
echo "<h2>Timezone Conversion Test</h2>";
$testTime = get_utc_now_string();

$commonZones = get_common_timezones();
echo "<table border='1'>";
echo "<tr><th>Timezone</th><th>Location</th><th>Converted Time</th></tr>";
foreach ($commonZones as $tz => $name) {
    echo "<tr>";
    echo "<td>" . $tz . "</td>";
    echo "<td>" . $name . "</td>";
    echo "<td>" . format_datetime_for_user($testTime, $tz) . "</td>";
    echo "</tr>";
}
echo "</table>";

// Test user timezone detection
echo "<h2>User Timezone Detection</h2>";
$userTz = detect_user_timezone();
echo "<p><strong>Detected User Timezone:</strong> " . $userTz . "</p>";

// Test timezone validation
echo "<h2>Timezone Validation Test</h2>";
$testZones = ['UTC', 'America/New_York', 'Europe/London', 'Asia/Tokyo', 'Invalid/Timezone'];
foreach ($testZones as $tz) {
    $valid = is_valid_timezone($tz);
    echo "<p><strong>" . $tz . ":</strong> " . ($valid ? '<span style="color: green;">Valid</span>' : '<span style="color: red;">Invalid</span>') . "</p>";
}

// Instructions for users
echo "<h2>How to Handle Timezone Differences</h2>";
echo "<ul>";
echo "<li><strong>Server Time:</strong> All times are stored in UTC for consistency</li>";
echo "<li><strong>User Experience:</strong> Times are converted to user's timezone when displayed</li>";
echo "<li><strong>Token Expiry:</strong> 5-minute grace period prevents issues with slight time differences</li>";
echo "<li><strong>Global Users:</strong> System works regardless of user's location</li>";
echo "</ul>";

echo "<h2>Benefits of This Approach</h2>";
echo "<ul>";
echo "<li>✅ Consistent server-side time handling</li>";
echo "<li>✅ User-friendly time display in their timezone</li>";
echo "<li>✅ Grace periods prevent false expirations</li>";
echo "<li>✅ Works for users worldwide</li>";
echo "<li>✅ Easy to maintain and debug</li>";
echo "</ul>";
?>