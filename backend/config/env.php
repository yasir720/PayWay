<?php
/**
 * Environment configuration loader.
 * Loads environment variables from .env file using phpdotenv.
 */

require __DIR__ . '/../vendor/autoload.php';

// Initialize Dotenv with project root directory
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../../');
// Load environment variables into $_ENV and $_SERVER
$dotenv->load();
