<?php

/**
 * Vercel serverless entry point for Laravel.
 * @see https://github.com/vercel-community/php
 */
chdir(dirname(__DIR__));

require __DIR__.'/../public/index.php';
