/**
 * ═════════════════════════════════════════════════════════════════════════════════
 * GUÍA DE CONFIGURACIÓN HOSTINGER – PAW AGENT STORYTELLING PRO
 * ═════════════════════════════════════════════════════════════════════════════════
 * 
 * Pasos detallados para poner en funcionamiento el sistema en Hostinger
 * 
 */

// ═════════════════════════════════════════════════════════════════════════════════
// PASO 1: CREAR TABLA EN BASE DE DATOS
// ═════════════════════════════════════════════════════════════════════════════════

📍 EN: Hostinger → cPanel → phpMyAdmin

1. Abre phpMyAdmin
2. Selecciona tu base de datos: u115767692_el_guero_bot
3. Abre la pestaña "SQL" o "Nueva consulta"
4. Pega este código:

───────────────────────────────────────────────────────────────────────────────

CREATE TABLE IF NOT EXISTS `knowledge_base` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nombre` varchar(255) NOT NULL,
  `tipo` varchar(50) DEFAULT 'storytelling',
  `storytelling` longtext,
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_storytelling` (`nombre`, `tipo`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

───────────────────────────────────────────────────────────────────────────────

5. Clickea "Ejecutar"
6. Deberías ver: "Tabla `knowledge_base` creada exitosamente"

✅ TABLA CREADA


// ═════════════════════════════════════════════════════════════════════════════════
// PASO 2: CONFIGURAR VARIABLES DE ENTORNO
// ═════════════════════════════════════════════════════════════════════════════════

📍 EN: Hostinger → cPanel → Variables de Entorno (o .env)

OPCIÓN A: A través de cPanel
──────────────────────────────

1. En cPanel, busca "Variables de Entorno" o "Environment Variables"
2. Agrega estas variables:

   DIFY_API_KEY = app-uAuHKtsI6l82PIqdF7e7yiVL
   DB_HOST = localhost
   DB_NAME = u115767692_el_guero_bot
   DB_USER = u115767692_lacueva
   DB_PASS = eldesmadredelGuero1

3. Salva cada una

✅ O MEJOR: A través de archivo .env

OPCIÓN B: Crear archivo .env (RECOMENDADO)
──────────────────────────────────────────

1. Conecta vía FTP a tu servidor
2. En la raíz de public_html, crea: .env
3. Pega esto:

───────────────────────────────────────────────────────────────────────────────

DIFY_API_KEY=app-uAuHKtsI6l82PIqdF7e7yiVL
DB_HOST=localhost
DB_NAME=u115767692_el_guero_bot
DB_USER=u115767692_lacueva
DB_PASS=eldesmadredelGuero1

───────────────────────────────────────────────────────────────────────────────

4. Guarda el archivo
5. Verifica que esté protegido por .htaccess (ya lo está)

✅ VARIABLES CONFIGURADAS


// ═════════════════════════════════════════════════════════════════════════════════
// PASO 3: VERIFICAR ESTRUCTURA DE CARPETAS
// ═════════════════════════════════════════════════════════════════════════════════

Tu estructura debe verse así:

```
public_html/
├── .env                          ← CREAR (variables)
├── .env.example                  ← YA EXISTE
├── .gitignore                    ← YA EXISTE
├── .htaccess                     ← YA ACTUALIZADO (máxima seguridad)
├── index.html
├── storytelling-invitado.html    ← NUEVO
├── diagnostico.php               ← Verificación
│
├── config/
│   ├── config.php                ← YA EXISTE (ACTUALIZADO)
│   └── README.md
│
├── css/
│   ├── paw-agent.css
│   └── storytelling-invitado.css ← NUEVO
│
├── paw-agent/
│   ├── paw-core.js               ← ACTUALIZADO
│   ├── paw-agent.js              ← ACTUALIZADO
│   ├── paw-modes.js              ← ACTUALIZADO
│   ├── paw-storytelling.js        ← NUEVO
│   ├── paw-chat.js
│   ├── paw-api.js
│   └── storytelling-invitado-page.js ← NUEVO
│
├── api/
│   ├── api-el-guero-bot.php
│   └── api-guero-knowledge.php   ← ACTUALIZADO
│
├── js/
│   ├── scripts.js
│   └── ... (otros)
│
└── logs/
    └── (se crea automáticamente)
```

✅ ESTRUCTURA VERIFICADA


// ═════════════════════════════════════════════════════════════════════════════════
// PASO 4: PROBAR CONFIGURACIÓN
// ═════════════════════════════════════════════════════════════════════════════════

1. EN TU NAVEGADOR, ve a:
   https://lacuevadelguero.com/diagnostico.php

2. Deberías ver:
   ✅ Archivo config.php existe
   ✅ DIFY_API_KEY definida: app-uAuHKt...
   ✅ Configuración BD
   ✅ Conexión a BD exitosa
   ✅ Tabla 'knowledge_base' existe
   ✅ Todas las extensiones PHP necesarias

   Si ves ❌ algo, verifica:
   - Que .env está en la raíz
   - Que la BD tiene las credenciales correctas
   - Que las extensiones (PDO, curl) están instaladas

✅ CONFIGURACIÓN VERIFICADA


// ═════════════════════════════════════════════════════════════════════════════════
// PASO 5: GENERAR PRIMER STORYTELLING
// ═════════════════════════════════════════════════════════════════════════════════

OPCIÓN A: Formulario HTML (MÁS FÁCIL PARA PROBAR)
──────────────────────────────────────────────────

1. Ve a: https://lacuevadelguero.com/storytelling-invitado.html

2. Rellena el formulario con datos de prueba:
   - Nombre: Tu nombre
   - Ocupación: Tu ocupación
   - Frase: Una frase que te describa
   - etc...

3. Clickea: "🚀 Generar Paquete"

4. Deberías ver los 5 documentos generados:
   ✅ 🎙 PRESENTACIÓN
   ✅ 🔥 STORYTELLING
   ✅ 🧩 GUION
   ✅ 🎬 ESCALETA
   ✅ 📇 CUE CARDS

5. Prueba:
   - 📋 Copiar Resultado
   - 💾 Descargar TXT
   - 🧹 Limpiar

✅ O: Conversacional en PAW Agent
────────────────────────────────

1. En el PAW Agent (esquina de tu página), escribe:
   "haz storytelling"

2. El Güero Bot te pregunta: "¿A qué te dedicas?"

3. Responde cada pregunta

4. Después de 13 respuestas: genera automáticamente

✅ PRIMER STORYTELLING GENERADO


// ═════════════════════════════════════════════════════════════════════════════════
// PASO 6: VERIFICAR GUARDADO EN BD
// ═════════════════════════════════════════════════════════════════════════════════

1. Abre phpMyAdmin
2. Ve a: Base de datos → knowledge_base → Explorar (Browse)
3. Deberías ver una fila con:
   - nombre: [tu nombre]
   - tipo: storytelling
   - storytelling: [JSON con los 5 documentos]
   - created_at: [fecha/hora actual]

✅ DATOS GUARDADOS EN BD


// ═════════════════════════════════════════════════════════════════════════════════
// PASO 7: VERIFICAR SEGURIDAD
// ═════════════════════════════════════════════════════════════════════════════════

Verifica que la seguridad está en lugar:

1. INTENTA acceder a config.php directamente:
   https://lacuevadelguero.com/config/config.php
   
   ❌ DEBE MOSTRAR: "Acceso denegado" o Error 403

2. INTENTA acceder a .env:
   https://lacuevadelguero.com/.env
   
   ❌ DEBE MOSTRAR: Nada o Error 403

3. INTENTA acceder a /logs/:
   https://lacuevadelguero.com/logs/
   
   ❌ DEBE MOSTRAR: Error 403

✅ SEGURIDAD VERIFICADA


// ═════════════════════════════════════════════════════════════════════════════════
// PASO 8: INTEGRACIÓN CON INVITADOS (OPCIONAL)
// ═════════════════════════════════════════════════════════════════════════════════

Si quieres que el storytelling aparezca en el modal de invitados:

1. ABRE: index.html
2. BUSCA: función abrirModalEscaleta()
3. EN el modal de búsqueda, agrega un botón:

   <button onclick="generarStorytellingDesdeInvitado('${inv.nombre}')">
       Generar Storytelling
   </button>

4. Esto reutiliza datos existentes de invitados

✅ INTEGRACIÓN OPCIONAL (PARA DESPUÉS)


// ═════════════════════════════════════════════════════════════════════════════════
// CHECKLIST FINAL DE CONFIGURACIÓN
// ═════════════════════════════════════════════════════════════════════════════════

Marca cada uno cuando completes:

□ Tabla knowledge_base creada en BD
□ Variables de entorno configuradas (.env o cPanel)
□ Archivo .htaccess actualizado
□ Carpeta /config/ protegida
□ Visitaste diagnostico.php (sin errores)
□ Probaste formulario HTML (storytelling-invitado.html)
□ Generaste primer storytelling
□ Datos guardaron en BD
□ Intentaste acceder a config.php → 403 ✓
□ PAW Agent storytelling funciona

✅ TODO COMPLETADO


// ═════════════════════════════════════════════════════════════════════════════════
// COMANDOS ÚTILES (SI NECESITAS)
// ═════════════════════════════════════════════════════════════════════════════════

📝 RESETEAR TABLA (BORRAR TODO):
─────────────────────────────────
En phpMyAdmin, ejecuta:

TRUNCATE TABLE knowledge_base;

⚠️ CUIDADO: Esto borra todos los storytellings guardados


📝 VER ESTRUCTURA DE TABLA:
────────────────────────────
En phpMyAdmin, usa: DESCRIBE knowledge_base;

Deberías ver:
- id
- nombre
- tipo
- storytelling (LONGTEXT)
- created_at
- updated_at


📝 BUSCAR UN STORYTELLING ESPECÍFICO:
──────────────────────────────────────
SELECT nombre, tipo, created_at FROM knowledge_base
WHERE tipo='storytelling'
ORDER BY created_at DESC;


📝 EXPORTAR UN STORYTELLING:
────────────────────────────
SELECT storytelling FROM knowledge_base
WHERE nombre='Juan' AND tipo='storytelling';


// ═════════════════════════════════════════════════════════════════════════════════
// TROUBLESHOOTING
// ═════════════════════════════════════════════════════════════════════════════════

❌ ERROR: "Conexión a BD fallida"
✅ SOLUCIONES:
   1. Verifica credenciales en .env
   2. Verifica que la BD existe: u115767692_el_guero_bot
   3. Verifica usuario/password en cPanel
   4. Prueba conectando desde phpMyAdmin

❌ ERROR: "404 Not Found" en storytelling-invitado.html
✅ SOLUCIONES:
   1. Verifica que el archivo existe: /storytelling-invitado.html
   2. Verifica que los imports CSS/JS están correctos
   3. Revisa la consola (F12) para ver errores

❌ ERROR: "Storytelling no se guarda"
✅ SOLUCIONES:
   1. Verifica tabla knowledge_base existe
   2. Revisa permisos de tabla (debe ser escribible)
   3. Abre Console (F12) para ver error de API
   4. Verifica api-guero-knowledge.php funciona

❌ ERROR: "config.php es accesible"
✅ SOLUCIONES:
   1. Verifica .htaccess está en /config/
   2. Verifica mod_rewrite está habilitado en Apache
   3. Si no funciona: cambia permisos de archivo a 644

❌ ERROR: "Diagnostico.php dice 'No encontrado'"
✅ SOLUCIONES:
   1. Verifica que diagnostico.php existe en raíz
   2. Accede a: https://lacuevadelguero.com/diagnostico.php
   3. Si la tabla no existe, créala en phpMyAdmin

// ═════════════════════════════════════════════════════════════════════════════════
// SOPORTE Y AYUDA
// ═════════════════════════════════════════════════════════════════════════════════

Si algo no funciona:

1. Abre la consola (F12) en tu navegador
2. Busca errores rojos
3. Revisa /logs/error.log en tu servidor
4. Intenta diagnostico.php para ver qué falla
5. Verifica los archivos en el orden de este documento

═════════════════════════════════════════════════════════════════════════════════
¡CONFIGURACIÓN LISTA PARA FUNCIONAR! 🚀
═════════════════════════════════════════════════════════════════════════════════
*/
