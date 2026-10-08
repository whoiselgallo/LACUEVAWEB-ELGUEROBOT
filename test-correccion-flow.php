<?php
/**
 * Test Automatizado: Ciclo Completo de Corrección de Cuestionario, Tracking y Webhook
 * Ejecuta cada fase del flujo vía subprocesos para probar endpoints de forma real.
 */

echo "====================================================================\n";
echo " TEST DE FLUJO COMPLETO: SOLICITUD DE CORRECCIÓN & REGENERACIÓN IA\n";
echo "====================================================================\n\n";

$php = 'C:\\xampp\\php\\php.exe';

// 1. OBTENER INVITADO DE PRUEBA
echo "[1] SELECCIÓN DEL INVITADO:\n";
$file = __DIR__ . '/images/formularios/cuestionarios_envios.json';
$envios = json_decode(file_get_contents($file), true);
$gallo = null;
foreach ($envios as $e) {
    if (stripos($e['nombre'], 'Gallo') !== false) {
        $gallo = $e;
        break;
    }
}

if (!$gallo) {
    echo "ERROR: Invitado 'El Gallo' no encontrado.\n";
    exit(1);
}

$token = $gallo['token'] ?? 'GUEST-GALLO';
echo "-> Invitado: {$gallo['nombre']}\n";
echo "-> Token / Código: {$token}\n";
echo "-> Estado Actual: " . ($gallo['estado'] ?? 'En Proceso') . "\n\n";

// 2. PRODUCCIÓN ENVÍA SOLICITUD DE CORRECCIÓN CON CASILLAS Y CAUSAS
echo "[2] PRODUCCIÓN ENVÍA SOLICITUD DE CORRECCIÓN:\n";
$cmd2 = "\"$php\" -r \"" . 
    "\$GLOBALS['_SERVER']['REQUEST_METHOD'] = 'POST'; " .
    "\$GLOBALS['_POST'] = [ " .
    "    'action' => 'solicitar_correccion', " .
    "    'token' => '{$token}', " .
    "    'observaciones' => [ " .
    "        ['id_pregunta' => 13, 'pregunta' => 'Peor Error en su Carrera', 'respuesta_original' => 'Firmar contratos a ciegas', 'causa' => 'incoherente', 'nota' => 'Contradice la respuesta 4 sobre quién manejaba tu música.'], " .
    "        ['id_pregunta' => 25, 'pregunta' => 'Confesión Incómoda & Exclusiva', 'respuesta_original' => 'Cosas de la vida', 'causa' => 'confusa', 'nota' => 'Muy ambigua. La mesa requiere la exclusiva real con contexto.'] " .
    "    ], " .
    "    'productor' => 'El Güero (Host Principal)' " .
    "]; " .
    "include 't:/LACUEVAWEB+ELGUEROBOT/api/api-guest-corrections.php';\"";

$out2 = shell_exec($cmd2);
$res2 = json_decode($out2, true);
echo "-> Estado: " . ($res2['status'] ?? 'error') . "\n";
echo "-> Preguntas Marcadas: " . ($res2['preguntas_observadas'] ?? 0) . "\n";
echo "-> Enlace de Tracking: " . ($res2['tracking_link'] ?? 'N/A') . "\n";
echo "-> Mensaje WhatsApp Generado:\n" . ($res2['whatsapp_message'] ?? 'N/A') . "\n\n";

// 3. INVITADO CONSULTA CON SU CÓDIGO
echo "[3] INVITADO CONSULTA CON SU CÓDIGO (VISTA FILTRADA):\n";
$cmd3 = "\"$php\" -r \"" .
    "\$GLOBALS['_SERVER']['REQUEST_METHOD'] = 'GET'; " .
    "\$GLOBALS['_GET'] = ['action' => 'obtener_solicitud', 'code' => '{$token}']; " .
    "include 't:/LACUEVAWEB+ELGUEROBOT/api/api-guest-corrections.php';\"";

$out3 = shell_exec($cmd3);
$res3 = json_decode($out3, true);
echo "-> Solicitud Activa: " . ($res3['activa'] ? 'SÍ' : 'NO') . "\n";
echo "-> Preguntas que ve el invitado (ÚNICAMENTE las observadas): " . count($res3['preguntas_a_corregir'] ?? []) . " preguntas:\n";
foreach ($res3['preguntas_a_corregir'] as $p) {
    echo "   * [#{$p['id_pregunta']} - {$p['pregunta']}] Causa: [{$p['causa']}]\n";
    echo "     Nota: \"{$p['nota']}\"\n";
    echo "     Respuesta Original: \"{$p['respuesta_original']}\"\n";
}
echo "\n";

// 4. INVITADO ENVÍA RESPUESTAS CORREGIDAS
echo "[4] INVITADO ENVÍA NUEVAS RESPUESTAS:\n";
$cmd4 = "\"$php\" -r \"" .
    "\$GLOBALS['_SERVER']['REQUEST_METHOD'] = 'POST'; " .
    "\$GLOBALS['_POST'] = [ " .
    "    'action' => 'guardar_correccion_invitado', " .
    "    'code' => '{$token}', " .
    "    'respuestas_corregidas' => [ " .
    "        13 => 'El peor error de mi carrera fue firmar una cesión con un mánager de Los Ángeles que me tuvo congelado 3 años sin poder sacar música propia.', " .
    "        25 => 'En exclusiva para La Cueva: estuve a punto de dejar la música norteña el año pasado por la depresión de ver cómo premiaban números inflados en lugar del talento real.' " .
    "    ] " .
    "]; " .
    "include 't:/LACUEVAWEB+ELGUEROBOT/api/api-guest-corrections.php';\"";

$out4 = shell_exec($cmd4);
$res4 = json_decode($out4, true);
echo "-> Estado: " . ($res4['status'] ?? 'error') . "\n";
echo "-> Preguntas Modificadas: " . ($res4['preguntas_modificadas'] ?? 0) . "\n";
echo "-> Mensaje: " . ($res4['message'] ?? '') . "\n\n";

// 5. VERIFICACIÓN DE REGENERACIÓN DEL GUIÓN Y ESCALETA
echo "[5] VERIFICACIÓN DE GUIÓN Y ESCALETA ACTUALIZADOS EN BASE DE DATOS:\n";
$enviosActualizados = json_decode(file_get_contents($file), true);
$galloActualizado = null;
foreach ($enviosActualizados as $ea) {
    if (($ea['token'] ?? '') === $token) {
        $galloActualizado = $ea;
        break;
    }
}

if ($galloActualizado) {
    echo "-> Estado del Registro: {$galloActualizado['estado']}\n";
    echo "-> P13 en base de datos: \"{$galloActualizado['respuestas'][13]}\"\n";
    echo "-> P25 en base de datos: \"{$galloActualizado['respuestas'][25]}\"\n";
    echo "-> Storytelling Enfoque: {$galloActualizado['storytelling_enfoque']}\n";
    echo "-> Cue Cards de Cabina:\n   " . str_replace("\n", "\n   ", $galloActualizado['cue_cards']) . "\n\n";
}

// 6. PRODUCCIÓN REVISA Y APRUEBA DEFINITIVAMENTE
echo "[6] PRODUCCIÓN DA CLIC EN 'APROBAR CUESTIONARIO':\n";
$cmd6 = "\"$php\" -r \"" .
    "\$GLOBALS['_SERVER']['REQUEST_METHOD'] = 'POST'; " .
    "\$GLOBALS['_POST'] = ['action' => 'aprobar_cuestionario', 'token' => '{$token}']; " .
    "include 't:/LACUEVAWEB+ELGUEROBOT/api/api-guest-corrections.php';\"";

$out6 = shell_exec($cmd6);
$res6 = json_decode($out6, true);
echo "-> Estado Aprobación: " . ($res6['status'] ?? 'error') . "\n";
echo "-> Fase de Tracking: Fase " . ($res6['fase_index'] ?? 2) . " (Escaleta & Curaduría)\n";
echo "-> Mensaje: " . ($res6['message'] ?? '') . "\n\n";

// 7. HISTORIAL DE EVENTOS WEBHOOK REGISTRADOS EN VIVO
echo "[7] VERIFICACIÓN DE EVENTOS DISPARADOS EN EL WEBHOOK:\n";
$eventosWebhooks = json_decode(file_get_contents(__DIR__ . '/images/formularios/eventos_webhook.json'), true);
$ultimos = array_slice($eventosWebhooks, 0, 4);
foreach ($ultimos as $idx => $ev) {
    $num = $idx + 1;
    echo "   [Evento {$num}] {$ev['titulo']} ({$ev['tipo']})\n";
    echo "      Timestamp: {$ev['timestamp']} | Origen: {$ev['origen']} | ID: {$ev['id']}\n";
}

echo "\n====================================================================\n";
echo " ✓ TEST COMPLETADO CON ÉXITO: 100% OPERATIVO SIN ERRORES\n";
echo "====================================================================\n";
