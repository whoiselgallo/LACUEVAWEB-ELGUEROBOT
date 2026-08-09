<?php
/**
 * Script de inicialización del Gestor de Blog
 * Crea las carpetas necesarias y verifica permisos
 */

echo "🔧 Inicializando Gestor de Blog...\n\n";

// Crear carpeta uploads/pdfs/
$upload_dir = dirname(__DIR__) . '/uploads';
$pdf_dir = $upload_dir . '/pdfs';

// Crear directorio uploads
if (!is_dir($upload_dir)) {
    if (mkdir($upload_dir, 0755, true)) {
        echo "✅ Carpeta 'uploads' creada\n";
    } else {
        echo "❌ Error al crear carpeta 'uploads'\n";
    }
} else {
    echo "✅ Carpeta 'uploads' ya existe\n";
}

// Crear directorio uploads/pdfs
if (!is_dir($pdf_dir)) {
    if (mkdir($pdf_dir, 0755, true)) {
        echo "✅ Carpeta 'uploads/pdfs' creada\n";
    } else {
        echo "❌ Error al crear carpeta 'uploads/pdfs'\n";
    }
} else {
    echo "✅ Carpeta 'uploads/pdfs' ya existe\n";
}

// Crear archivo posts.json si no existe
$posts_file = dirname(__DIR__) . '/posts.json';
if (!file_exists($posts_file)) {
    if (file_put_contents($posts_file, json_encode([], JSON_PRETTY_PRINT))) {
        echo "✅ Archivo 'posts.json' creado\n";
    } else {
        echo "❌ Error al crear archivo 'posts.json'\n";
    }
} else {
    echo "✅ Archivo 'posts.json' ya existe\n";
}

// Verificar permisos
echo "\n📋 Verificación de permisos:\n";
echo (is_writable($upload_dir) ? "✅" : "❌") . " Carpeta 'uploads' escribible\n";
echo (is_writable($pdf_dir) ? "✅" : "❌") . " Carpeta 'uploads/pdfs' escribible\n";
echo (is_writable($posts_file) ? "✅" : "❌") . " Archivo 'posts.json' escribible\n";

// Información del sistema
echo "\n🖥️  Información del Sistema:\n";
echo "PHP Version: " . phpversion() . "\n";
echo "OS: " . php_uname() . "\n";

// Verificar extensiones necesarias
echo "\n📦 Extensiones disponibles:\n";
echo (extension_loaded('json') ? "✅" : "❌") . " JSON\n";
echo (extension_loaded('curl') ? "✅" : "❌") . " CURL\n";

echo "\n✨ Inicialización completada.\n";
echo "Accede a: manage-blog.html\n";
?>
