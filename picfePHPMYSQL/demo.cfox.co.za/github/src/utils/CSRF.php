<?php
class CSRF {
    const TOKEN_NAME = 'csrf_token';
    const TOKEN_LENGTH = 32;

    /**
     * Generate a new CSRF token and store it in session
     */
    public static function generateToken() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (empty($_SESSION[self::TOKEN_NAME])) {
            $_SESSION[self::TOKEN_NAME] = bin2hex(random_bytes(self::TOKEN_LENGTH));
        }

        return $_SESSION[self::TOKEN_NAME];
    }

    /**
     * Get the current CSRF token from session
     */
    public static function getToken() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        return $_SESSION[self::TOKEN_NAME] ?? self::generateToken();
    }

    /**
     * Validate a CSRF token against the session token
     */
    public static function validateToken($token) {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (empty($_SESSION[self::TOKEN_NAME])) {
            return false;
        }

        return hash_equals($_SESSION[self::TOKEN_NAME], $token);
    }

    /**
     * Validate CSRF token from POST data
     */
    public static function validateRequest() {
        $token = $_POST[self::TOKEN_NAME] ??
                 $_SERVER['HTTP_X_CSRF_TOKEN'] ??
                 '';

        // Also check for the exact header name that might be sent
        if (empty($token) && function_exists('getallheaders')) {
            $headers = getallheaders();
            $token = $headers['X-CSRF-Token'] ?? $headers['X-CSRF-TOKEN'] ?? '';
        }

        if (empty($token)) {
            return false;
        }

        return self::validateToken($token);
    }

    /**
     * Regenerate the CSRF token (useful after login/logout)
     */
    public static function regenerateToken() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $_SESSION[self::TOKEN_NAME] = bin2hex(random_bytes(self::TOKEN_LENGTH));
        return $_SESSION[self::TOKEN_NAME];
    }

    /**
     * Get HTML input field for CSRF token
     */
    public static function getTokenField() {
        $token = self::getToken();
        return '<input type="hidden" name="' . self::TOKEN_NAME . '" value="' . htmlspecialchars($token) . '" />';
    }

    /**
     * Get meta tag for CSRF token (for AJAX requests)
     */
    public static function getTokenMeta() {
        $token = self::getToken();
        return '<meta name="csrf-token" content="' . htmlspecialchars($token) . '" />';
    }
}
?>