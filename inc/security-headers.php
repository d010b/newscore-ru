<?php
/**
 * Security Headers для темы NewsCore
 */

// Безопасные заголовки
function newscore_security_headers($headers) {
    if (!headers_sent()) {
        $headers['X-Content-Type-Options'] = 'nosniff';
        $headers['X-Frame-Options'] = 'SAMEORIGIN';
        $headers['X-XSS-Protection'] = '1; mode=block';
        $headers['Referrer-Policy'] = 'strict-origin-when-cross-origin';
        
        // Content Security Policy (опционально, может требовать настройки)
        // $headers['Content-Security-Policy'] = "default-src 'self'; script-src 'self'; style-src 'self'";
    }
    return $headers;
}

// Защита от SQL инъекций
function newscore_sql_protection($query) {
    if (is_search() && !empty($query->query_vars['s'])) {
        $query->query_vars['s'] = sanitize_text_field($query->query_vars['s']);
    }
    return $query;
}

// Валидация email
function newscore_validate_email($email) {
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        return false;
    }
    return filter_var($email, FILTER_SANITIZE_EMAIL);
}

// Санитизация массива
function newscore_sanitize_array($array) {
    if (!is_array($array)) {
        return sanitize_text_field($array);
    }
    
    foreach ($array as $key => $value) {
        if (is_array($value)) {
            $array[$key] = newscore_sanitize_array($value);
        } else {
            $array[$key] = sanitize_text_field($value);
        }
    }
    
    return $array;
}