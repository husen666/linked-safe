#!/usr/bin/env bash
set -euo pipefail

if [[ $# -lt 1 ]]; then
  echo "Usage: $0 <staging|prod>"
  exit 1
fi

ENVIRONMENT="$1"
if [[ "$ENVIRONMENT" != "staging" && "$ENVIRONMENT" != "prod" ]]; then
  echo "ENVIRONMENT must be staging or prod"
  exit 1
fi

REPO_ROOT="$(cd "$(dirname "${BASH_SOURCE[0]}")/../.." && pwd)"
cd "$REPO_ROOT"

ENV_FILE=".env.${ENVIRONMENT}"
COMPOSE_FILE="deploy/docker-compose.${ENVIRONMENT}.yml"

if [[ ! -f "$ENV_FILE" ]]; then
  echo "Missing ${ENV_FILE}. Copy from deploy/.env.${ENVIRONMENT}.example first."
  exit 1
fi

if [[ ! -f "$COMPOSE_FILE" ]]; then
  echo "Missing ${COMPOSE_FILE}"
  exit 1
fi

source "$ENV_FILE"

: "${SITE_ARCHIVE:?SITE_ARCHIVE is required in ${ENV_FILE}}"
: "${SQL_ZIP:?SQL_ZIP is required in ${ENV_FILE}}"
: "${CERT_ZIP:?CERT_ZIP is required in ${ENV_FILE}}"
: "${APP_URL:?APP_URL is required in ${ENV_FILE}}"

WP_REDIS_HOST_VAL="${WP_REDIS_HOST:-redis}"
WP_REDIS_PORT_VAL="${WP_REDIS_PORT:-6379}"
WP_REDIS_DATABASE_VAL="${WP_REDIS_DATABASE:-0}"
WP_REDIS_PASSWORD_VAL="${WP_REDIS_PASSWORD:-${REDIS_PASSWORD:-}}"
WP_REDIS_PREFIX_VAL="${WP_REDIS_PREFIX:-linkedsafe_${ENVIRONMENT}:}"
WP_CACHE_KEY_SALT_VAL="${WP_CACHE_KEY_SALT:-linkedsafe_${ENVIRONMENT}:}"

REDIS_PASSWORD_DEFINE=""
if [[ -n "$WP_REDIS_PASSWORD_VAL" ]]; then
  REDIS_PASSWORD_DEFINE="define( 'WP_REDIS_PASSWORD', '${WP_REDIS_PASSWORD_VAL}' );\n"
fi

[[ -f "$SITE_ARCHIVE" ]] || { echo "Missing file: $SITE_ARCHIVE (upload backup file to server bak/ directory first)"; exit 1; }
[[ -f "$SQL_ZIP" ]] || { echo "Missing file: $SQL_ZIP (upload backup file to server bak/ directory first)"; exit 1; }
[[ -f "$CERT_ZIP" ]] || { echo "Missing file: $CERT_ZIP (upload certificate zip to server bak/ directory first)"; exit 1; }

mkdir -p site mysql-init "ssl/${ENVIRONMENT}" tmp
rm -rf site/* tmp/*

tar -xzf "$SITE_ARCHIVE" -C site

unzip -oq "$SQL_ZIP" -d tmp
SQL_FILE="$(find tmp -maxdepth 1 -type f -name '*.sql' | head -n 1)"
[[ -n "$SQL_FILE" ]] || { echo "No .sql file found in $SQL_ZIP"; exit 1; }
cp -f "$SQL_FILE" mysql-init/01-init.sql

rm -rf "ssl/${ENVIRONMENT}"/*
unzip -oq "$CERT_ZIP" -d "ssl/${ENVIRONMENT}"

WP_CONFIG="site/wp-config.php"
if [[ -f "$WP_CONFIG" ]]; then
  sed -i "s/define( 'DB_HOST', 'localhost' );/define( 'DB_HOST', 'db:3306' );/g" "$WP_CONFIG" || true
  sed -i "/define( 'WP_MEMORY_LIMIT'/d;/define( 'WP_MAX_MEMORY_LIMIT'/d;/define( 'WP_HOME'/d;/define( 'WP_SITEURL'/d;/define( 'WP_CACHE'/d;/define( 'WP_REDIS_HOST'/d;/define( 'WP_REDIS_PORT'/d;/define( 'WP_REDIS_DATABASE'/d;/define( 'WP_REDIS_PASSWORD'/d;/define( 'WP_REDIS_PREFIX'/d;/define( 'WP_CACHE_KEY_SALT'/d;/define( 'DISABLE_WP_CRON'/d;/HTTP_X_FORWARDED_PROTO/d;/define( 'WP_DEBUG', false );/d;/define('WP_DEBUG', false);/d" "$WP_CONFIG"

  sed -i "/stop editing/i if (isset(\$_SERVER['HTTP_X_FORWARDED_PROTO']) \&\& \$_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https') { \$_SERVER['HTTPS'] = 'on'; }\ndefine( 'WP_DEBUG', false );\ndefine( 'WP_MEMORY_LIMIT', '512M' );\ndefine( 'WP_MAX_MEMORY_LIMIT', '512M' );\ndefine( 'WP_HOME', '${APP_URL}' );\ndefine( 'WP_SITEURL', '${APP_URL}' );\ndefine( 'WP_CACHE', true );\ndefine( 'WP_REDIS_HOST', '${WP_REDIS_HOST_VAL}' );\ndefine( 'WP_REDIS_PORT', ${WP_REDIS_PORT_VAL} );\ndefine( 'WP_REDIS_DATABASE', ${WP_REDIS_DATABASE_VAL} );\n${REDIS_PASSWORD_DEFINE}define( 'WP_REDIS_PREFIX', '${WP_REDIS_PREFIX_VAL}' );\ndefine( 'WP_CACHE_KEY_SALT', '${WP_CACHE_KEY_SALT_VAL}' );\ndefine( 'DISABLE_WP_CRON', true );" "$WP_CONFIG"
fi

docker compose --env-file "$ENV_FILE" -f "$COMPOSE_FILE" up -d
docker compose --env-file "$ENV_FILE" -f "$COMPOSE_FILE" ps

echo "First deployment completed for ${ENVIRONMENT}."
