<?php
/**
 * Timezone Helper Functions
 * Handles timezone conversions and time-sensitive operations
 */

/**
 * Get current UTC timestamp
 */
function get_utc_now() {
    return new DateTime('now', new DateTimeZone('UTC'));
}

/**
 * Get current UTC time as string
 */
function get_utc_now_string() {
    return get_utc_now()->format('Y-m-d H:i:s');
}

/**
 * Convert UTC datetime string to user's timezone
 */
function utc_to_user_timezone($utc_datetime, $user_timezone = null) {
    if (!$user_timezone) {
        $user_timezone = DEFAULT_USER_TIMEZONE;
    }

    try {
        $utc_date = new DateTime($utc_datetime, new DateTimeZone('UTC'));
        $utc_date->setTimezone(new DateTimeZone($user_timezone));
        return $utc_date;
    } catch (Exception $e) {
        // Fallback to UTC if timezone is invalid
        return new DateTime($utc_datetime, new DateTimeZone('UTC'));
    }
}

/**
 * Format datetime for display in user's timezone
 */
function format_datetime_for_user($utc_datetime, $user_timezone = null, $format = 'Y-m-d H:i:s T') {
    $user_date = utc_to_user_timezone($utc_datetime, $user_timezone);
    return $user_date->format($format);
}

/**
 * Check if a UTC datetime is expired (with optional grace period)
 */
function is_expired($utc_datetime, $grace_minutes = 0) {
    $now = get_utc_now();
    $expiry = new DateTime($utc_datetime, new DateTimeZone('UTC'));

    if ($grace_minutes > 0) {
        $expiry->add(new DateInterval('PT' . $grace_minutes . 'M'));
    }

    return $now > $expiry;
}

/**
 * Get time remaining until expiry in user-friendly format
 */
function get_time_remaining($utc_datetime, $user_timezone = null) {
    $now = get_utc_now();
    $expiry = new DateTime($utc_datetime, new DateTimeZone('UTC'));

    if ($now >= $expiry) {
        return 'Expired';
    }

    $interval = $now->diff($expiry);

    if ($interval->days > 0) {
        return $interval->format('%d days, %h hours');
    } elseif ($interval->h > 0) {
        return $interval->format('%h hours, %i minutes');
    } elseif ($interval->i > 0) {
        return $interval->format('%i minutes');
    } else {
        return 'Less than 1 minute';
    }
}

/**
 * Detect user's timezone from browser/client hints (if available)
 * This is a basic implementation - in production you might want to use JavaScript
 */
function detect_user_timezone() {
    // Check for timezone in session
    if (isset($_SESSION['user_timezone'])) {
        return $_SESSION['user_timezone'];
    }

    // Check for timezone in cookie
    if (isset($_COOKIE['user_timezone'])) {
        return $_COOKIE['user_timezone'];
    }

    // Default to UTC
    return DEFAULT_USER_TIMEZONE;
}

/**
 * Set user's timezone preference
 */
function set_user_timezone($timezone) {
    // Validate timezone
    try {
        new DateTimeZone($timezone);
        $_SESSION['user_timezone'] = $timezone;

        // Set cookie for persistence (30 days)
        setcookie('user_timezone', $timezone, time() + (30 * 24 * 60 * 60), '/');

        return true;
    } catch (Exception $e) {
        return false;
    }
}

/**
 * Get list of common timezones for user selection
 */
function get_common_timezones() {
    return [
        'UTC' => 'UTC',
        'America/New_York' => 'Eastern Time (ET)',
        'America/Chicago' => 'Central Time (CT)',
        'America/Denver' => 'Mountain Time (MT)',
        'America/Los_Angeles' => 'Pacific Time (PT)',
        'Europe/London' => 'London (GMT/BST)',
        'Europe/Paris' => 'Paris (CET/CEST)',
        'Europe/Berlin' => 'Berlin (CET/CEST)',
        'Asia/Tokyo' => 'Tokyo (JST)',
        'Asia/Shanghai' => 'Shanghai (CST)',
        'Asia/Kolkata' => 'India (IST)',
        'Australia/Sydney' => 'Sydney (AEST/AEDT)',
        'Africa/Johannesburg' => 'South Africa (SAST)',
    ];
}

/**
 * Validate timezone string
 */
function is_valid_timezone($timezone) {
    try {
        new DateTimeZone($timezone);
        return true;
    } catch (Exception $e) {
        return false;
    }
}
?>