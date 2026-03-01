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

  if ! grep -q "define( 'WP_HOME'" "$WP_CONFIG"; then
    sed -i "/stop editing/i define( 'WP_HOME', '${APP_URL}' );\ndefine( 'WP_SITEURL', '${APP_URL}' );" "$WP_CONFIG"
  fi
fi

docker compose --env-file "$ENV_FILE" -f "$COMPOSE_FILE" up -d
docker compose --env-file "$ENV_FILE" -f "$COMPOSE_FILE" ps

echo "First deployment completed for ${ENVIRONMENT}."
