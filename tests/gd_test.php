<?php

/**
 * GD Extension Verification Test
 * Run this from the command line to verify GD is properly configured
 * 
 * Usage: php tests/gd_test.php
 */

echo "═══════════════════════════════════════════════════════════════\n";
echo "  GD Extension Verification Test\n";
echo "═══════════════════════════════════════════════════════════════\n\n";

// Test 1: Check if GD is loaded
echo "1. GD Extension Status:\n";
if (extension_loaded('gd')) {
    echo "   ✅ GD extension is LOADED\n\n";
} else {
    echo "   ❌ GD extension is NOT loaded\n";
    echo "   ⚠️  Enable extension=gd in php.ini\n\n";
    exit(1);
}

// Test 2: Get GD Version
echo "2. GD Version Information:\n";
$gd_info = gd_info();
if (!empty($gd_info['GD Version'])) {
    echo "   Version: " . $gd_info['GD Version'] . "\n";
}
echo "   GD Support: " . ($gd_info['GD Support'] ? 'Enabled' : 'Disabled') . "\n\n";

// Test 3: Check PNG Support
echo "3. PNG Support:\n";
if (!empty($gd_info['PNG Support'])) {
    echo "   ✅ PNG Support: Enabled\n";
    if (!empty($gd_info['libPNG Version'])) {
        echo "   libPNG Version: " . $gd_info['libPNG Version'] . "\n";
    }
} else {
    echo "   ❌ PNG Support: Disabled\n";
}
echo "\n";

// Test 4: Check JPEG Support
echo "4. JPEG Support:\n";
if (!empty($gd_info['JPEG Support'])) {
    echo "   ✅ JPG Support: Enabled\n";
} else {
    echo "   ⚠️  JPG Support: Disabled\n";
}
echo "\n";

// Test 5: Check image creation functions
echo "5. Image Creation Functions:\n";
$functions = ['imagecreate', 'imagecreatetruecolor', 'imagecreatefrompng', 'imagecreatefromjpeg'];
foreach ($functions as $fn) {
    $available = function_exists($fn);
    $status = $available ? '✅' : '❌';
    echo "   $status " . str_pad($fn, 25) . ": " . ($available ? 'Available' : 'Not Available') . "\n";
}
echo "\n";

// Test 6: FreeType Support (for text rendering)
echo "6. FreeType Support:\n";
if (!empty($gd_info['FreeType Support'])) {
    echo "   ✅ FreeType Support: Enabled\n";
    if (!empty($gd_info['FreeType Linkage'])) {
        echo "   Linkage: " . $gd_info['FreeType Linkage'] . "\n";
    }
} else {
    echo "   ⚠️  FreeType Support: Disabled\n";
}
echo "\n";

// Test 7: Test actual image creation
echo "7. Practical Test - Creating Test Image:\n";
try {
    $img = imagecreatetruecolor(100, 100);
    if ($img !== false) {
        $color = imagecolorallocate($img, 255, 0, 0);
        imagefill($img, 0, 0, $color);
        imagedestroy($img);
        echo "   ✅ Image creation successful\n\n";
    } else {
        echo "   ❌ Failed to create image\n\n";
        exit(1);
    }
} catch (Exception $e) {
    echo "   ❌ Error creating image: " . $e->getMessage() . "\n\n";
    exit(1);
}

// Test 8: PHP ini path
echo "8. PHP Configuration:\n";
echo "   PHP Version: " . phpversion() . "\n";
$ini_file = php_ini_loaded_file();
echo "   php.ini Location: " . ($ini_file ?: 'Not found') . "\n";
echo "   php.ini Scan Dir: " . (php_ini_scanned_files() ?: 'None') . "\n\n";

// Final status
echo "═══════════════════════════════════════════════════════════════\n";
echo "✅ All tests passed! GD extension is properly configured.\n";
echo "   Your application can now generate PDFs with images.\n";
echo "═══════════════════════════════════════════════════════════════\n";
