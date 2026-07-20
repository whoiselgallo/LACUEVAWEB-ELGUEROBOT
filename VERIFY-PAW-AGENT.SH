#!/bin/bash
# SCRIPT DE VERIFICACIÓN - Agente Huella de Perro Neon
# Verificar que todos los archivos estén en su lugar

echo "╔════════════════════════════════════════════════════════════╗"
echo "║  ✅ VERIFICACIÓN DE IMPLEMENTACIÓN - PAW AGENT            ║"
echo "╚════════════════════════════════════════════════════════════╝"
echo ""

# Array de archivos requeridos
FILES=(
    "paw-agent.js"
    "paw-agent.css"
    "index.html"
    "PAW-AGENT-README.md"
    "IMPLEMENTACION-COMPLETA.md"
)

echo "📋 Verificando archivos requeridos:"
echo ""

for file in "${FILES[@]}"; do
    if [ -f "$file" ]; then
        echo "✅ $file"
    else
        echo "❌ $file - NO ENCONTRADO"
    fi
done

echo ""
echo "═════════════════════════════════════════════════════════════"
echo ""

# Verificar contenidos específicos
echo "🔍 Verificando contenidos clave:"
echo ""

if grep -q "paw-agent.css" index.html; then
    echo "✅ index.html incluye referencia a paw-agent.css"
else
    echo "❌ index.html NO incluye referencia a paw-agent.css"
fi

if grep -q "paw-agent.js" index.html; then
    echo "✅ index.html incluye referencia a paw-agent.js"
else
    echo "❌ index.html NO incluye referencia a paw-agent.js"
fi

if grep -q "pawAgentContainer" index.html; then
    echo "✅ index.html incluye el contenedor de la huella"
else
    echo "❌ index.html NO incluye el contenedor de la huella"
fi

if grep -q "neon-pulse-magenta-cyan" paw-agent.css; then
    echo "✅ paw-agent.css incluye animación neon"
else
    echo "❌ paw-agent.css NO incluye animación neon"
fi

if grep -q "initPawAgent" paw-agent.js; then
    echo "✅ paw-agent.js incluye función de inicialización"
else
    echo "❌ paw-agent.js NO incluye función de inicialización"
fi

echo ""
echo "═════════════════════════════════════════════════════════════"
echo ""

# Estadísticas de líneas
echo "📊 Estadísticas de código:"
echo ""
echo "paw-agent.js: $(wc -l < paw-agent.js) líneas"
echo "paw-agent.css: $(wc -l < paw-agent.css) líneas"
echo ""

echo "═════════════════════════════════════════════════════════════"
echo ""
echo "✅ VERIFICACIÓN COMPLETADA"
echo ""
echo "Para usar la nueva interfaz:"
echo "1. Abre index.html en tu navegador"
echo "2. Mira la huella de perro neon en la esquina inferior derecha"
echo "3. Interactúa con los 5 dedos: 📤 💬 🎯 👤 ⭐"
echo "4. Escribe en el chat y presiona Enter"
echo ""
