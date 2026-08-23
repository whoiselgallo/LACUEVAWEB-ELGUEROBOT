<?php
/**
 * DATABASE SEEDER: MIGRACIÓN DE 8 CUESTIONARIOS DE INVITADOS A LA CUEVA
 * Endpoint/Script: /api/seeder-cuestionarios.php
 * Ejecución: Web o CLI
 */

header('Content-Type: text/plain; charset=utf-8');
require_once __DIR__ . '/../config/config.php';

try {
    $pdo = db_connect();
    echo "✓ Conectado a la base de datos PostgreSQL exitosamente.\n";
} catch (Exception $e) {
    die("❌ Error de conexión: " . $e->getMessage() . "\n");
}

$jsonFile = __DIR__ . '/../images/formularios/cuestionarios.json';
if (!file_exists($jsonFile)) {
    die("❌ Archivo de cuestionarios no encontrado en: $jsonFile\n");
}

$data = json_decode(file_get_contents($jsonFile), true);
if (json_last_error() !== JSON_ERROR_NONE) {
    die("❌ Error decodificando cuestionarios.json: " . json_last_error_msg() . "\n");
}

echo "✓ Cargados " . count($data) . " cuestionarios para sembrar.\n\n";

foreach ($data as $idx => $q) {
    // 1. Extraer y limpiar nombre
    $rawName = trim($q['No bre'] ?? $q['Nombre'] ?? '');
    if (empty($rawName) || $rawName === 'Nombre') {
        continue;
    }
    
    // Normalizar capitalización
    $nombre = ucwords(strtolower($rawName));
    $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $nombre), '-'));
    
    echo "=== Procesando Invitado # " . ($idx + 1) . ": $nombre ===\n";
    
    // 2. Mapear campos de Excel a estructura relacional
    $ocupacion = trim($q['¿A qué te dedicas actualmente?'] ?? 'Invitado Especial');
    $barrioColonia = trim($q['¿De qué colonia eres?'] ?? 'Mexicali');
    $barrioDef = trim($q['Que significa para ti el Barrio?'] ?? '');
    $barrio = $barrioColonia . ($barrioDef ? " (Significa: $barrioDef)" : "");
    
    // Trayectoria compilada
    $trayectoria = "• Enseñanza mayor: " . trim($q['¿Cuál es la mayor enseñanza que te ha re'] ?? '') . "\n" .
                   "• De niño quería ser: " . trim($q['Que quería ser de niño, el tu de 10 años'] ?? '') . "\n" .
                   "• Primer logro: " . trim($q['¿Cual fue tu primer logro? Ese momento e'] ?? '') . "\n" .
                   "• Cumpliendo sueños de niño: " . trim($q['En estos momentos: ¿Estas cumpliendo lo '] ?? '') . "\n" .
                   "• Si perdiera el rumbo: " . trim($q['En el camino a veces perdemos el rumbo y'] ?? '');
                   
    // Herida de infancia y retos
    $herida = "• Obstáculos/Bullying: " . trim($q['Hubo alguien que se burlo o te dijo que '] ?? '') . "\n" .
              "• Camino difícil: " . trim($q[' El Camino y los madrazos: ¿Qué es lo má'] ?? '') . "\n" .
              "• Sacrificios: " . trim($q['¿Cuáles son esas cosas que tuviste que s'] ?? '') . "\n" .
              "• Mayor defecto: " . trim($q['¿Cuál consideras que es tu mayor defecto'] ?? '') . "\n" .
              "• Miedos: " . trim($q['¿A qué le tienes miedo y cómo lo enfrent'] ?? '') . "\n" .
              "• Molestia: " . trim($q['Lo que más te molesta:'] ?? '');
              
    // Confesión incómoda y lado oscuro
    $incomodo = "• Confesión: " . trim($q['Confesión incomoda: Cuentanos eso que si'] ?? '') . "\n" .
                "• Lado oculto: " . trim($q['Todos tenemos algo que nos gusta, pero n'] ?? '');
                
    // Gustos musicales y vibras
    $gustos = trim($q['¿Esa canción que escuchas y te pones "bá'] ?? 'Música urbana variada');
    
    // Ficha técnica estructurada
    $definicion = trim($q['¿Como te defines en 3 palabras?'] ?? '');
    $feliz = trim($q['¿Eres feliz o aun te faltan sueños por r'] ?? '');
    $diferente = trim($q['¿Quá te hace diferente a los de más, tie'] ?? '');
    $chusco = trim($q['Algo chusco o chistoso que te haya pasad'] ?? '');
    $recordar = trim($q['¿Como te gustaría que la gente te recuer'] ?? '');
    $ayuda = trim($q['Es momento de la ayuda, que le dirias a '] ?? '');
    
    $ficha = "### FICHA TÉCNICA DE PRODUCCIÓN\n" .
             "**Definición (3 palabras):** $definicion\n" .
             "**Estado de Felicidad/Sueños:** $feliz\n" .
             "**Diferenciador:** $diferente\n" .
             "**Anécdota graciosa:** $chusco\n" .
             "**Legado deseado:** $recordar\n" .
             "**Mensaje de ayuda social:** $ayuda\n";
             
    // Fechas propuestas
    $fecha_nacimiento = trim($q['Column'] ?? '');
    $fecha_propuesta = date('Y-m-d', strtotime('+' . ($idx * 7) . ' days')); // Espaciados semanalmente
    
    // Signo Zodiacal derivado básico
    $signo = "Mexicali Flow";
    
    // 3. Insertar o actualizar en la tabla invitados
    try {
        $checkStmt = $pdo->prepare("SELECT id FROM invitados WHERE nombre = :nombre");
        $checkStmt->execute([':nombre' => $nombre]);
        $exist = $checkStmt->fetch();
        
        if ($exist) {
            $stmt = $pdo->prepare("
                UPDATE invitados SET
                    ocupacion = :ocupacion,
                    signo = :signo,
                    fecha_nacimiento = :fecha_nacimiento,
                    barrio = :barrio,
                    trayectoria = :trayectoria,
                    herida = :herida,
                    incomodo = :incomodo,
                    gustos = :gustos,
                    fecha_propuesta = :fecha_propuesta,
                    ficha = :ficha
                WHERE nombre = :nombre
            ");
            echo "   -> Actualizando registro existente en DB...\n";
        } else {
            $stmt = $pdo->prepare("
                INSERT INTO invitados 
                (nombre, ocupacion, signo, fecha_nacimiento, barrio, trayectoria, herida, incomodo, gustos, fecha_propuesta, ficha)
                VALUES 
                (:nombre, :ocupacion, :signo, :fecha_nacimiento, :barrio, :trayectoria, :herida, :incomodo, :gustos, :fecha_propuesta, :ficha)
            ");
            echo "   -> Insertando nuevo registro en DB...\n";
        }
        
        $stmt->execute([
            ':nombre' => $nombre,
            ':ocupacion' => $ocupacion,
            ':signo' => $signo,
            ':fecha_nacimiento' => $fecha_nacimiento,
            ':barrio' => $barrio,
            ':trayectoria' => $trayectoria,
            ':herida' => $herida,
            ':incomodo' => $incomodo,
            ':gustos' => $gustos,
            ':fecha_propuesta' => $fecha_propuesta,
            ':ficha' => $ficha
        ]);
        
    } catch (Exception $dbEx) {
        echo "   ⚠️ Error en base de datos para $nombre: " . $dbEx->getMessage() . "\n";
    }
    
    // 4. Crear estructura de carpetas físicas según flujo de producción de La Cueva
    $dirEscaleta = __DIR__ . "/../escaleta/$slug";
    $dirAssets = __DIR__ . "/../images/invitados/$slug";
    
    if (!is_dir($dirEscaleta)) {
        mkdir($dirEscaleta, 0777, true);
        echo "   -> Creada carpeta de guiones y escaleta: /escaleta/$slug\n";
    }
    if (!is_dir($dirAssets)) {
        mkdir($dirAssets, 0777, true);
        echo "   -> Creada carpeta de assets visuales: /images/invitados/$slug\n";
    }
    
    // Escribir archivo de resumen de producción (checklist)
    $readmeContent = "========================================================\n" .
                     "FLUJO DE PRODUCCIÓN DE LA CUEVA - INVITADO: $nombre\n" .
                     "========================================================\n\n" .
                     "Paso 1: [ ] Cuestionario inicial contestado e indexado (COMPLETADO ✓)\n" .
                     "Paso 2: [ ] Ficha técnica y Storytelling cargado en DB (COMPLETADO ✓)\n" .
                     "Paso 3: [ ] Redacción de guion de preguntas personalizadas.\n" .
                     "Paso 4: [ ] Grabación del episodio en set.\n" .
                     "Paso 5: [ ] Masterización del audio y edición multicámara.\n" .
                     "Paso 6: [ ] Generación de Hooks y publicación en redes.\n\n" .
                     "DATOS DEL INVITADO:\n" .
                     "- Nombre: $nombre\n" .
                     "- Ocupación: $ocupacion\n" .
                     "- Barrio: $barrio\n" .
                     "- Canción favorita: $gustos\n";
                     
    file_put_contents("$dirEscaleta/README-PRODUCCION.txt", $readmeContent);
    file_put_contents("$dirEscaleta/cuestionario-original.json", json_encode($q, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    
    echo "   -> Guardados archivos de control de producción.\n\n";
}

echo "✓ Siembra de base de datos y creación de carpetas completada exitosamente.\n";
?>
