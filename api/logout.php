<?php
// Bridge for Vercel Serverless Function to execute dashboard/logout.php
chdir(__DIR__ . '/../dashboard');
require __DIR__ . '/../dashboard/logout.php';
