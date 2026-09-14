#!/bin/bash
# ═══════════════════════════════════════════════════════════════════════
# SCRIPT DE RESTAURACIÓN DE CHECKPOINT - LA CUEVA DEL GÜERO
# Regresa el servidor y el código a este punto 100% estable
# ═══════════════════════════════════════════════════════════════════════

set -e

echo ""
echo "════════════════════════════════════════════════════════════"
echo "  RESTAURANDO AL CHECKPOINT ESTABLE DE LA CUEVA DEL GÜERO  "
echo "════════════════════════════════════════════════════════════"
echo ""

cd /var/www/html

echo "[1/4] Descartando cambios locales o archivos temporales..."
git reset --hard
git clean -fd

echo "[2/4] Sincronizando al Checkpoint Estable..."
git fetch origin --tags
git checkout clean-main
git reset --hard checkpoint-estable

echo "[3/4] Ajustando permisos de carpetas..."
chmod -R 775 /var/www/html 2>/dev/null || true

echo "[4/4] Probando conexión a la Base de Datos Neon..."
php api/api-db-test.php

echo ""
echo "════════════════════════════════════════════════════════════"
echo "  ✅ PROYECTO RESTAURADO EXITOSAMENTE AL CHECKPOINT ESTABLE "
echo "════════════════════════════════════════════════════════════"
echo ""
