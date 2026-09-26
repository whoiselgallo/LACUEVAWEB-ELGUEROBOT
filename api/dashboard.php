<?php
// Bridge for Vercel Serverless Function to execute dashboard/index.php
chdir(__DIR__ . '/../dashboard');
require __DIR__ . '/../dashboard/index.php';
