#!/usr/bin/env php
<?php

// This script helps identify which Blade files need to be converted to Vue
$viewsBackup = 'resources/views_backup';
$pagesDir = 'resources/js/Pages';

$adminBladeFiles = glob("$viewsBackup/admin/**/*.blade.php");
$tenantBladeFiles = glob("$viewsBackup/tenant/**/*.blade.php");
$authBladeFiles = glob("$viewsBackup/auth/**/*.blade.php");
$publicBladeFiles = glob("$viewsBackup/public/**/*.blade.php");

echo "=== Admin Blade Files ===\n";
foreach ($adminBladeFiles as $file) {
    echo str_replace($viewsBackup . '/', '', $file) . "\n";
}

echo "\n=== Tenant Blade Files ===\n";
foreach ($tenantBladeFiles as $file) {
    echo str_replace($viewsBackup . '/', '', $file) . "\n";
}

echo "\n=== Auth Blade Files ===\n";
foreach ($authBladeFiles as $file) {
    echo str_replace($viewsBackup . '/', '', $file) . "\n";
}

echo "\n=== Public Blade Files ===\n";
foreach ($publicBladeFiles as $file) {
    echo str_replace($viewsBackup . '/', '', $file) . "\n";
}

echo "\nTotal files to convert: " . (count($adminBladeFiles) + count($tenantBladeFiles) + count($authBladeFiles) + count($publicBladeFiles)) . "\n";
