<?php

/*
 * Keep this file out of any public repository once you add a real key.
 * Prefer setting OPENROUTER_API_KEY in Apache/XAMPP's environment instead.
 */
define('CHATBOT_MODEL', 'openai/gpt-4o-mini');

$localConfig = __DIR__ . '/chatbot_config.local.php';
if (is_file($localConfig)) {
    require_once $localConfig;
}

define('CHATBOT_API_KEY', getenv('OPENROUTER_API_KEY') ?: (defined('CHATBOT_LOCAL_API_KEY') ? CHATBOT_LOCAL_API_KEY : ''));
