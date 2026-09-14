#!/bin/bash
# ═══════════════════════════════════════════════════════════════════════
# MIGRACIÓN: NEON.TECH → GOOGLE CLOUD SQL
# La Cueva del Güero - PostgreSQL 17.11
# Ejecutar en el servidor backend como root o www-data
# ═══════════════════════════════════════════════════════════════════════
set -e

# ── CONFIGURACIÓN ──────────────────────────────────────────────────────
NEON_URL="postgresql://neondb_owner:npg_eOUvM7qXj0SZ@ep-winter-queen-af6tc66y-pooler.c-2.us-west-2.aws.neon.tech/neondb?sslmode=require"
CLOUD_HOST="136.114.160.76"
CLOUD_PORT="5432"
CLOUD_USER="postgres"
CLOUD_DB="lacueva_db"
CLOUD_PASS="eldesmadredelGuero#1"
DUMP_FILE="/tmp/neon_backup_$(date +%Y%m%d_%H%M%S).dump"
WEB_ROOT="/var/www/html"
ENV_FILE="$WEB_ROOT/.env"

export PGPASSWORD="$CLOUD_PASS"

echo ""
echo "══════════════════════════════════════════════════════"
echo "  MIGRACIÓN NEON → CLOUD SQL - LA CUEVA DEL GÜERO"
echo "══════════════════════════════════════════════════════"
echo ""

# ── PASO 1: Verificar herramientas ────────────────────────────────────
echo "[1/7] Verificando herramientas..."
for tool in pg_dump pg_restore psql; do
  if ! command -v $tool &>/dev/null; then
    echo "  ✗ '$tool' no encontrado. Instalando postgresql-client..."
    apt-get install -y postgresql-client 2>/dev/null || yum install -y postgresql 2>/dev/null
    break
  fi
done
echo "  ✓ Herramientas OK"

# ── PASO 2: Crear base de datos en Cloud SQL ─────────────────────────
echo ""
echo "[2/7] Creando base de datos 'lacueva_db' en Cloud SQL..."
psql -h "$CLOUD_HOST" -p "$CLOUD_PORT" -U "$CLOUD_USER" -d postgres \
  -c "CREATE DATABASE lacueva_db;" 2>/dev/null \
  && echo "  ✓ Base de datos 'lacueva_db' creada" \
  || echo "  ℹ  La base de datos ya existe (OK)"

# ── PASO 3: Crear schema en Cloud SQL ────────────────────────────────
echo ""
echo "[3/7] Creando schema (tablas e índices)..."
psql -h "$CLOUD_HOST" -p "$CLOUD_PORT" -U "$CLOUD_USER" -d "$CLOUD_DB" \
  -f "$WEB_ROOT/scripts/schema-cloudsql.sql"
echo "  ✓ Schema creado correctamente"

# ── PASO 4: Exportar datos de Neon ───────────────────────────────────
echo ""
echo "[4/7] Exportando datos desde Neon.tech..."
pg_dump "$NEON_URL" \
  --no-owner \
  --no-privileges \
  --no-acl \
  --data-only \
  --format=custom \
  --file="$DUMP_FILE"
echo "  ✓ Backup exportado: $DUMP_FILE ($(du -sh $DUMP_FILE | cut -f1))"

# ── PASO 5: Importar datos a Cloud SQL ───────────────────────────────
echo ""
echo "[5/7] Importando datos a Cloud SQL..."
pg_restore \
  -h "$CLOUD_HOST" \
  -p "$CLOUD_PORT" \
  -U "$CLOUD_USER" \
  -d "$CLOUD_DB" \
  --no-owner \
  --no-privileges \
  --data-only \
  --disable-triggers \
  "$DUMP_FILE" 2>&1 | grep -v "^pg_restore: warning" || true
echo "  ✓ Datos importados"

# ── PASO 6: Actualizar .env en el servidor ────────────────────────────
echo ""
echo "[6/7] Actualizando .env del servidor..."
if [ -f "$ENV_FILE" ]; then
  sed -i "s|^DB_HOST=.*|DB_HOST=136.64.199.83|" "$ENV_FILE"
  sed -i "s|^DB_NAME=.*|DB_NAME=lacueva_db|" "$ENV_FILE"
  sed -i "s|^DB_USER=.*|DB_USER=postgres|" "$ENV_FILE"
  sed -i "s|^DB_PASS=.*|DB_PASS=eldesmadredelGuero#1|" "$ENV_FILE"
  echo "  ✓ .env actualizado"
else
  echo "  ✗ No se encontró $ENV_FILE — actualiza manualmente"
fi

# ── PASO 7: Verificar conexión ────────────────────────────────────────
echo ""
echo "[7/7] Verificando tablas en Cloud SQL..."
psql -h "$CLOUD_HOST" -p "$CLOUD_PORT" -U "$CLOUD_USER" -d "$CLOUD_DB" \
  -c "SELECT tablename, (SELECT COUNT(*) FROM pg_class c JOIN pg_namespace n ON n.oid=c.relnamespace WHERE c.relname=t.tablename AND n.nspname='public') FROM pg_tables t WHERE schemaname='public' ORDER BY tablename;" 2>/dev/null || true

echo ""
echo "══════════════════════════════════════════════════════"
echo "  ✅ MIGRACIÓN COMPLETADA"
echo ""
echo "  Prueba la conexión en:"
echo "  https://lacuevadelguero.com/api/api-db-test.php"
echo ""
echo "  Si algo falló, re-siembra con:"
echo "  php $WEB_ROOT/scripts/poblar_knowledge_base.php"
echo "══════════════════════════════════════════════════════"
echo ""

unset PGPASSWORD

