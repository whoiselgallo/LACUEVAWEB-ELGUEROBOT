<?php
/**
 * SYNC v2 - Sincroniza invitados a knowledge_base con ponderación 0-9
 * Regenera los registros de storytelling con la nueva estructura completa:
 * - alias, storytelling_enfoque, reto, frase
 * - ponderación con 9 criterios evaluados
 * - escaleta, guion, cue_cards actualizados
 */
require_once __DIR__ . '/../config/config.php';

try {
    $db = db_connect();
    echo "✓ Conectado a Neon.tech\n";

    // ═══════════════════════════════════════════════════════════════
    // DEFINICIÓN DE PONDERACIONES POR INVITADO (basadas en cuestionario)
    // Criterios: 0=nulo, 1-3=bajo, 4-5=medio, 6-7=alto, 8-9=excepcional
    // ═══════════════════════════════════════════════════════════════

    $evaluaciones = [
        'Leo Camacho Higuera' => [
            'alias' => 'Leo',
            'storytelling_enfoque' => 'La lealtad de barrio y la identidad de Valle Dorado — cómo la familia y la calle forjan al hombre.',
            'reto' => 'Crecer en un entorno donde la línea entre lo correcto y lo incorrecto es difusa.',
            'frase' => '"El barrio no es la esquina, el barrio es la familia que te cuida la espalda."',
            'criterios' => [
                ['nombre' => 'Profundidad Narrativa', 'score' => 7, 'justificacion' => 'Tiene historia cruda de barrio con matices. Valle Dorado = Familia es un concepto potente para explotar narrativamente.'],
                ['nombre' => 'Autenticidad & Vulnerabilidad', 'score' => 8, 'justificacion' => 'Su ocupación declarada como "delinquiendo" muestra una honestidad brutal poco común. Se abre sin filtro.'],
                ['nombre' => 'Relevancia de Barrio', 'score' => 9, 'justificacion' => 'Valle Dorado es uno de los barrios más representativos de Mexicali. Su significado "Familia" es gold para el podcast.'],
                ['nombre' => 'Factor Entretenimiento', 'score' => 7, 'justificacion' => 'Su estilo directo y sin filtro genera momentos memorables. La combinación de humor callejero y profundidad es ganadora.'],
                ['nombre' => 'Potencial Viral (Hooks)', 'score' => 8, 'justificacion' => 'Frases callejeras auténticas, la tensión entre "delinquiendo" y "familia" es un hook de impacto para redes.'],
                ['nombre' => 'Diversidad de Tema', 'score' => 8, 'justificacion' => 'Aporta el ángulo de vida callejera real que pocos invitados pueden dar con esta autenticidad.'],
                ['nombre' => 'Conexión Emocional', 'score' => 7, 'justificacion' => 'Lo que le molesta son las mentiras y esperar — conecta con la impaciencia y hambre de la audiencia joven.'],
                ['nombre' => 'Claridad de Mensaje', 'score' => 6, 'justificacion' => 'El mensaje de familia como escudo está claro, pero necesita guión para estructurar bien los bloques.'],
                ['nombre' => 'Potencial de Continuidad', 'score' => 8, 'justificacion' => 'Da para episodio completo de 40+ minutos. Hay mucho qué explorar en su perspectiva de barrio y decisiones de vida.']
            ]
        ],
        'Javi Domz (jeyb)' => [
            'alias' => 'Jeyb',
            'storytelling_enfoque' => 'El camino del director creativo — de soñar en Hacienda Dorada a hacer cine y TV en la frontera.',
            'reto' => 'Las injusticias contra la gente vulnerable y convertir la rabia en arte.',
            'frase' => '"Tu casa es donde te abrigan, no donde naciste."',
            'criterios' => [
                ['nombre' => 'Profundidad Narrativa', 'score' => 9, 'justificacion' => 'Director de cine y cantautor desde la frontera. Su trayectoria creativa es material de documental.'],
                ['nombre' => 'Autenticidad & Vulnerabilidad', 'score' => 8, 'justificacion' => 'Le molestan las injusticias contra la gente vulnerable y las mentiras. Sensibilidad real que conecta.'],
                ['nombre' => 'Relevancia de Barrio', 'score' => 7, 'justificacion' => 'Hacienda Dorada es "Casa con corazón". Su visión de barrio como hogar emocional suma a la identidad del podcast.'],
                ['nombre' => 'Factor Entretenimiento', 'score' => 8, 'justificacion' => 'Creativo profesional — sabe contar historias, tiene timing natural y perspectiva visual única.'],
                ['nombre' => 'Potencial Viral (Hooks)', 'score' => 9, 'justificacion' => 'Director de cine hablando de barrio fronterizo = formato cruzado que atrae tanto audiencia urbana como cinéfila.'],
                ['nombre' => 'Diversidad de Tema', 'score' => 9, 'justificacion' => 'Único invitado del mundo audiovisual/artístico. Aporta un ángulo completamente diferente al catálogo.'],
                ['nombre' => 'Conexión Emocional', 'score' => 8, 'justificacion' => 'Su pasión por defender vulnerables y transformar experiencias en arte genera empatía inmediata.'],
                ['nombre' => 'Claridad de Mensaje', 'score' => 8, 'justificacion' => 'Mensaje claro: el arte como vehículo de cambio social desde la frontera. La audiencia se lo lleva fácil.'],
                ['nombre' => 'Potencial de Continuidad', 'score' => 9, 'justificacion' => 'Da para episodio completo + serie de seguimiento. Se puede vincular con estreno de proyectos futuros.']
            ]
        ],
        'Marcelo Ivan Maciel Maldonado' => [
            'alias' => 'Marcelo',
            'storytelling_enfoque' => 'La justicia administrativa vista desde el barrio — crecer en Leonardo Guillén y llegar al tribunal.',
            'reto' => 'Enfrentar la falta de humildad en un sistema donde el poder corrompe.',
            'frase' => '"Respetarnos como comunidad es el primer acto de justicia."',
            'criterios' => [
                ['nombre' => 'Profundidad Narrativa', 'score' => 8, 'justificacion' => 'Tribunal estatal de justicia administrativa desde un barrio popular. El contraste es narrativamente rico.'],
                ['nombre' => 'Autenticidad & Vulnerabilidad', 'score' => 7, 'justificacion' => 'La falta de humildad como molestia principal revela una ética personal fuerte. Falta profundizar más en lo personal.'],
                ['nombre' => 'Relevancia de Barrio', 'score' => 8, 'justificacion' => 'Leonardo Guillén = "Respetarnos como comunidad". Su definición de barrio es la más social de todos los invitados.'],
                ['nombre' => 'Factor Entretenimiento', 'score' => 6, 'justificacion' => 'El tema jurídico puede ser denso. Necesita un guión que balancee lo institucional con anécdotas humanas.'],
                ['nombre' => 'Potencial Viral (Hooks)', 'score' => 7, 'justificacion' => 'Un magistrado hablando de respeto comunitario en un podcast de barrio tiene potencial de debate en redes.'],
                ['nombre' => 'Diversidad de Tema', 'score' => 9, 'justificacion' => 'Único representante del sector público/judicial. Aporta la perspectiva institucional que el catálogo necesita.'],
                ['nombre' => 'Conexión Emocional', 'score' => 7, 'justificacion' => 'Su lucha contra la arrogancia institucional resuena con quien ha sentido impotencia ante la burocracia.'],
                ['nombre' => 'Claridad de Mensaje', 'score' => 8, 'justificacion' => 'Mensaje contundente: la justicia empieza en el respeto comunitario, no en el tribunal. Fácil de transmitir.'],
                ['nombre' => 'Potencial de Continuidad', 'score' => 7, 'justificacion' => 'Da para episodio completo. Se puede vincular con temas de justicia social, derechos ciudadanos y barrio.']
            ]
        ],
        'Aurelio Gonzalez' => [
            'alias' => 'Aurelio',
            'storytelling_enfoque' => 'El origen de los negocios y trayectoria comercial en la frontera — la Carbajal como escuela de vida.',
            'reto' => 'Crisis iniciales de financiamiento y aprender a emprender sin red de seguridad.',
            'frase' => '"La constancia supera al talento."',
            'criterios' => [
                ['nombre' => 'Profundidad Narrativa', 'score' => 7, 'justificacion' => 'Carrocería automotriz desde la Carbajal. La 428k como identidad de calle tiene peso narrativo real.'],
                ['nombre' => 'Autenticidad & Vulnerabilidad', 'score' => 8, 'justificacion' => 'Le molesta la gente que aparenta lo que no son. Su autenticidad directa es la esencia de La Cueva.'],
                ['nombre' => 'Relevancia de Barrio', 'score' => 8, 'justificacion' => '"El barrio es la unión entre los que somos" — definición potente. La Carbajal es barrio icónico de Mexicali.'],
                ['nombre' => 'Factor Entretenimiento', 'score' => 7, 'justificacion' => '"Los pájaros nalgones" como molestia es gold para generar humor natural en la conversación.'],
                ['nombre' => 'Potencial Viral (Hooks)', 'score' => 7, 'justificacion' => 'Su estilo fronterizo y frases directas son material para clips de 30-60 segundos en redes.'],
                ['nombre' => 'Diversidad de Tema', 'score' => 7, 'justificacion' => 'Emprendimiento automotriz desde el barrio. Aporta el ángulo de negocio familiar en la frontera.'],
                ['nombre' => 'Conexión Emocional', 'score' => 7, 'justificacion' => 'La constancia como filosofía de vida conecta con emprendedores y trabajadores de la audiencia.'],
                ['nombre' => 'Claridad de Mensaje', 'score' => 8, 'justificacion' => '"La constancia supera al talento" es un mensaje cristalino que la audiencia se lleva inmediatamente.'],
                ['nombre' => 'Potencial de Continuidad', 'score' => 7, 'justificacion' => 'Da para episodio completo centrado en emprendimiento, barrio y la cultura automotriz de Mexicali.']
            ]
        ],
        'Guillermina Ayala Quiñonez' => [
            'alias' => 'Guille',
            'storytelling_enfoque' => 'La dignidad del trabajo invisible — ser empleada doméstica en la colonia Libertad.',
            'reto' => 'La lucha diaria de mantener una familia con trabajo doméstico en un barrio que "no significa nada" para ella.',
            'frase' => '"El trabajo no tiene cara, tiene manos."',
            'criterios' => [
                ['nombre' => 'Profundidad Narrativa', 'score' => 8, 'justificacion' => 'Empleada doméstica es el trabajo más invisible de México. Su historia es un espejo de millones de mujeres.'],
                ['nombre' => 'Autenticidad & Vulnerabilidad', 'score' => 9, 'justificacion' => 'Que el barrio "no signifique nada" para ella revela una crudeza y desencanto que es profundamente honesto.'],
                ['nombre' => 'Relevancia de Barrio', 'score' => 7, 'justificacion' => 'Colonia Libertad — nombre irónico dado que ella no siente conexión. Esa tensión es narrativamente valiosa.'],
                ['nombre' => 'Factor Entretenimiento', 'score' => 6, 'justificacion' => 'Menos espectacular que otros pero su historia es profundamente humana. Necesita buena conducción del Güero.'],
                ['nombre' => 'Potencial Viral (Hooks)', 'score' => 7, 'justificacion' => 'Una empleada doméstica diciendo la verdad cruda en un podcast tiene potencial de impacto social en TikTok/IG.'],
                ['nombre' => 'Diversidad de Tema', 'score' => 9, 'justificacion' => 'Única mujer trabajadora del sector doméstico. Perspectiva invisible que ningún otro invitado aporta.'],
                ['nombre' => 'Conexión Emocional', 'score' => 9, 'justificacion' => '"Que me desobedezcan" como molestia principal muestra a una mujer que lucha por respeto. Empatía máxima.'],
                ['nombre' => 'Claridad de Mensaje', 'score' => 7, 'justificacion' => 'El mensaje de dignidad laboral está ahí pero necesita ser articulado con el guión para que sea memorable.'],
                ['nombre' => 'Potencial de Continuidad', 'score' => 7, 'justificacion' => 'Da para episodio completo. El ángulo femenino y de trabajo invisible abre un tema nuevo para el canal.']
            ]
        ],
        'Sergio Rene Coronado Vega' => [
            'alias' => 'Rene',
            'storytelling_enfoque' => 'El día a día sin ocupación definida — la imprudencia como veneno social.',
            'reto' => 'Encontrar propósito cuando no tienes un título ni una ocupación que te defina.',
            'frase' => '"La familia no se escoge, pero se defiende."',
            'criterios' => [
                ['nombre' => 'Profundidad Narrativa', 'score' => 4, 'justificacion' => 'Ocupación "nada" y respuestas escuetas. Hay historia potencial pero el cuestionario no la revela.'],
                ['nombre' => 'Autenticidad & Vulnerabilidad', 'score' => 5, 'justificacion' => 'Declarar "nada" como ocupación es honesto pero no profundiza en el porqué. Falta apertura emocional.'],
                ['nombre' => 'Relevancia de Barrio', 'score' => 6, 'justificacion' => 'Hacienda Dorada = "Familia". Misma colonia que Jeyb pero sin la carga narrativa del mundo creativo.'],
                ['nombre' => 'Factor Entretenimiento', 'score' => 4, 'justificacion' => 'Respuestas breves en el cuestionario sugieren una personalidad reservada. Puede ser difícil de conducir.'],
                ['nombre' => 'Potencial Viral (Hooks)', 'score' => 5, 'justificacion' => 'La imprudencia como tema puede generar un par de clips pero no tiene el punch de otros invitados.'],
                ['nombre' => 'Diversidad de Tema', 'score' => 5, 'justificacion' => 'No aporta un ángulo claramente diferenciado del catálogo existente.'],
                ['nombre' => 'Conexión Emocional', 'score' => 5, 'justificacion' => 'La falta de ocupación puede conectar con jóvenes en la misma situación pero necesita más profundidad.'],
                ['nombre' => 'Claridad de Mensaje', 'score' => 4, 'justificacion' => 'No hay un mensaje claro que se pueda extraer del cuestionario. Requiere mucha conducción en vivo.'],
                ['nombre' => 'Potencial de Continuidad', 'score' => 3, 'justificacion' => 'Mejor como segmento corto (10 min) o como clip para redes. No alcanza para episodio completo.']
            ]
        ],
        'Sergio Noe Escobar Perez' => [
            'alias' => 'Noé',
            'storytelling_enfoque' => 'Migración, llantería y la vida del hondureño en Mexicali — de La Bomba a la frontera norte.',
            'reto' => 'Dejar Honduras, cruzar fronteras y construir una vida como llantero en tierra ajena.',
            'frase' => '"De donde vengo, la soberbia no te da de comer."',
            'criterios' => [
                ['nombre' => 'Profundidad Narrativa', 'score' => 9, 'justificacion' => 'Migración centroamericana + llantería + vida en Mexicali. Triple capa narrativa de alto impacto.'],
                ['nombre' => 'Autenticidad & Vulnerabilidad', 'score' => 8, 'justificacion' => 'Le molesta la hipocresía y soberbia. Un migrante hablando de humildad tiene peso moral enorme.'],
                ['nombre' => 'Relevancia de Barrio', 'score' => 8, 'justificacion' => 'De Honduras a Mexicali — su barrio es literalmente dos países. La migración como barrio expandido.'],
                ['nombre' => 'Factor Entretenimiento', 'score' => 7, 'justificacion' => 'Las historias de migración siempre enganchan. Su oficio de llantero aporta textura visual y humor.'],
                ['nombre' => 'Potencial Viral (Hooks)', 'score' => 8, 'justificacion' => 'Un hondureño llantero en Mexicali rechazando la soberbia = formato que rompe estereotipos en redes.'],
                ['nombre' => 'Diversidad de Tema', 'score' => 9, 'justificacion' => 'Único migrante centroamericano. Aporta la perspectiva internacional que amplía el alcance del canal.'],
                ['nombre' => 'Conexión Emocional', 'score' => 9, 'justificacion' => 'La historia de migración, trabajo honesto y rechazo a la hipocresía genera empatía inmediata y profunda.'],
                ['nombre' => 'Claridad de Mensaje', 'score' => 8, 'justificacion' => 'Trabajo honesto sin importar de dónde vienes. Mensaje claro y universal que trasciende fronteras.'],
                ['nombre' => 'Potencial de Continuidad', 'score' => 8, 'justificacion' => 'Da para episodio completo + mini-serie sobre migración. Se puede vincular con comunidad hondureña en Mxli.']
            ]
        ],
        'Yessica Lizbeth Fierro Vindiola' => [
            'alias' => 'Yessica',
            'storytelling_enfoque' => 'Crecer y sobresalir desde Puertas del Sol — la ama de casa como pilar invisible del barrio.',
            'reto' => 'Salir adelante como ama de casa en un mundo que no valora el trabajo del hogar.',
            'frase' => '"Donde uno crece, aprende a sobresalir."',
            'criterios' => [
                ['nombre' => 'Profundidad Narrativa', 'score' => 7, 'justificacion' => 'Ama de casa en Puertas del Sol. Su definición de barrio como lugar para "sobresalir" tiene carga aspiracional.'],
                ['nombre' => 'Autenticidad & Vulnerabilidad', 'score' => 8, 'justificacion' => 'Le molestan las mentiras e hipocresía. Una ama de casa hablando directo tiene autenticidad natural.'],
                ['nombre' => 'Relevancia de Barrio', 'score' => 7, 'justificacion' => 'Puertas del Sol = "Donde creces y aprendes a sobresalir". Definición optimista que contrasta con otros invitados.'],
                ['nombre' => 'Factor Entretenimiento', 'score' => 6, 'justificacion' => 'El tema de ama de casa puede ser menos espectacular pero es profundamente relatable. Necesita conducción cálida.'],
                ['nombre' => 'Potencial Viral (Hooks)', 'score' => 7, 'justificacion' => 'Una ama de casa dando verdades sobre mentiras e hipocresía tiene potencial de resonancia en audiencia femenina.'],
                ['nombre' => 'Diversidad de Tema', 'score' => 8, 'justificacion' => 'Segunda perspectiva femenina pero desde un ángulo diferente a Guillermina. Aporta visión aspiracional del barrio.'],
                ['nombre' => 'Conexión Emocional', 'score' => 8, 'justificacion' => 'La lucha por sobresalir desde el hogar conecta con madres y amas de casa — audiencia masiva subestimada.'],
                ['nombre' => 'Claridad de Mensaje', 'score' => 7, 'justificacion' => '"Donde creces, aprendes a sobresalir" es un mensaje claro pero necesita profundizarse en el episodio.'],
                ['nombre' => 'Potencial de Continuidad', 'score' => 7, 'justificacion' => 'Da para episodio completo centrado en la mujer del barrio, maternidad y aspiración familiar.']
            ]
        ]
    ];

    // ═══════════════════════════════════════════════════════════════
    // ACTUALIZAR registros existentes en knowledge_base
    // ═══════════════════════════════════════════════════════════════

    $updateStmt = $db->prepare("
        UPDATE knowledge_base 
        SET storytelling = ?, updated_at = CURRENT_TIMESTAMP 
        WHERE nombre = ? AND tipo = 'storytelling'
    ");

    $insertStmt = $db->prepare("
        INSERT INTO knowledge_base (nombre, tipo, storytelling, created_at, updated_at)
        VALUES (?, 'storytelling', ?, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP)
    ");

    $checkStmt = $db->prepare("SELECT id FROM knowledge_base WHERE nombre = ? AND tipo = 'storytelling'");

    // Obtener datos originales de invitados para la escaleta/guion/cuecards
    $invStmt = $db->query("SELECT * FROM invitados ORDER BY id");
    $invitados = [];
    foreach ($invStmt as $inv) {
        $invitados[$inv['nombre']] = $inv;
    }

    foreach ($evaluaciones as $nombre => $eval) {
        $inv = $invitados[$nombre] ?? [];

        // Calcular score total (promedio de los 9 criterios)
        $totalScore = 0;
        foreach ($eval['criterios'] as $c) {
            $totalScore += $c['score'];
        }
        $scorePromedio = round($totalScore / count($eval['criterios']), 1);

        // Determinar nivel basado en score
        if ($scorePromedio >= 7) {
            $nivel = 'ALTO';
            $badge = '🟢 NIVEL ALTO';
            $color = '#39FF14';
            $formato = 'Episodio Completo (40+ min)';
        } elseif ($scorePromedio >= 5) {
            $nivel = 'MEDIO';
            $badge = '🟡 NIVEL MEDIO';
            $color = '#00FFFF';
            $formato = 'Entrevista Corta / Segmento (10-20 min)';
        } else {
            $nivel = 'BAJO';
            $badge = '🔴 NIVEL BAJO';
            $color = '#FF00FF';
            $formato = 'Micro-contenido / Clips (1-5 min)';
        }

        $storyData = [
            'alias' => $eval['alias'],
            'storytelling_enfoque' => $eval['storytelling_enfoque'],
            'reto' => $eval['reto'],
            'frase' => $eval['frase'],
            'escaleta' => "### ESCALETA DE PRODUCCIÓN - LA CUEVA DEL GÜERO\n" .
                          "**Invitado:** {$nombre}\n" .
                          "**Alias:** {$eval['alias']}\n" .
                          "**Ocupación:** " . ($inv['ocupacion'] ?? 'Invitado Especial') . "\n" .
                          "**Barrio:** " . ($inv['barrio'] ?? 'Mexicali') . "\n" .
                          "**Enfoque Central:** {$eval['storytelling_enfoque']}\n\n" .
                          "#### BLOQUE 1: EL ORIGEN DEL BARRIO\n" .
                          ($inv['trayectoria'] ?? 'Preguntas de inicio sobre el barrio y raíces.') . "\n\n" .
                          "#### BLOQUE 2: LOS GOLPES DE LA VIDA\n" .
                          "Reto Principal: {$eval['reto']}\n" .
                          ($inv['herida'] ?? 'Experiencias de superación y momentos difíciles.') . "\n\n" .
                          "#### BLOQUE 3: LA ANÉCDOTA Y CIERRE\n" .
                          "Frase de Cierre: {$eval['frase']}\n" .
                          ($inv['incomodo'] ?? 'Confesión y legado.'),

            'guion' => "### GUIÓN PARA EL GÜERO Y EL JUNIOR\n" .
                       "**Invitado:** {$nombre} ({$eval['alias']})\n" .
                       "**Tema Central:** {$eval['storytelling_enfoque']}\n\n" .
                       "**[00:00] EL GÜERO:** ¡Qué tranza, mi gente de La Cueva! Hoy tenemos en la mesa a un compa bien pesado de " . ($inv['barrio'] ?? 'la cuadra') . ": {$nombre}.\n" .
                       "**[01:15] EL JUNIOR:** ¡A huevo, carnal! Platícanos {$eval['alias']}, ¿cómo te la rifabas de morro antes de estar en " . ($inv['ocupacion'] ?? 'tu jale actual') . "?\n\n" .
                       "**[05:00] MOMENTO CLAVE:** {$eval['reto']}\n\n" .
                       "**[15:00] PREGUNTA PICANTE:** " . ($inv['incomodo'] ?? 'Cuéntanos eso que casi nadie sabe de ti.') . "\n\n" .
                       "**[CIERRE] FRASE PARA LA AUDIENCIA:** {$eval['frase']}",

            'cue_cards' => "### CUE CARDS PARA EL SET\n" .
                           "• **Invitado:** {$nombre}\n" .
                           "• **Alias:** {$eval['alias']}\n" .
                           "• **Ocupación:** " . ($inv['ocupacion'] ?? '') . "\n" .
                           "• **Barrio:** " . ($inv['barrio'] ?? '') . "\n" .
                           "• **Enfoque:** {$eval['storytelling_enfoque']}\n" .
                           "• **Reto:** {$eval['reto']}\n" .
                           "• **Frase Cierre:** {$eval['frase']}\n" .
                           "• **Gustos:** " . ($inv['gustos'] ?? 'Música urbana') . "\n" .
                           "• **Molestia:** " . ($inv['molestia'] ?? ($inv['herida'] ?? '')) . "\n" .
                           "• **Score Curaduría:** {$scorePromedio}/9 ({$nivel})",

            'curaduria' => [
                'nivel' => $nivel,
                'badge' => $badge,
                'formato' => $formato,
                'color' => $color,
                'razon' => $eval['storytelling_enfoque']
            ],
            'ponderacion' => [
                'score_total' => $scorePromedio,
                'criterios' => $eval['criterios']
            ]
        ];

        $jsonStr = json_encode($storyData, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);

        $checkStmt->execute([$nombre]);
        if ($checkStmt->fetch()) {
            $updateStmt->execute([$jsonStr, $nombre]);
            echo "✓ Actualizado: {$nombre} (Score: {$scorePromedio}/9 — {$nivel})\n";
        } else {
            $insertStmt->execute([$nombre, $jsonStr]);
            echo "✓ Insertado: {$nombre} (Score: {$scorePromedio}/9 — {$nivel})\n";
        }
    }

    echo "\n═══════════════════════════════════════════\n";
    echo "Sincronización v2 completada exitosamente\n";
    echo "═══════════════════════════════════════════\n";

    // Verificar resultados
    $verify = $db->query("SELECT id, nombre FROM knowledge_base WHERE tipo='storytelling' ORDER BY id");
    echo "\nRegistros storytelling en knowledge_base:\n";
    foreach ($verify as $v) {
        echo "  ID {$v['id']}: {$v['nombre']}\n";
    }

} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}
