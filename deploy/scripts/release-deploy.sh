#!/usr/bin/env bash
set -euo pipefail

if [[ $# -lt 1 ]]; then
  echo "Usage: $0 <staging|prod> [git_ref]"
  exit 1
fi

ENVIRONMENT="$1"
GIT_REF="${2:-}"

if [[ "$ENVIRONMENT" != "staging" && "$ENVIRONMENT" != "prod" ]]; then
  echo "ENVIRONMENT must be staging or prod"
  exit 1
fi

REPO_ROOT="$(cd "$(dirname "${BASH_SOURCE[0]}")/../.." && pwd)"
cd "$REPO_ROOT"

ENV_FILE=".env.${ENVIRONMENT}"
COMPOSE_FILE="deploy/docker-compose.${ENVIRONMENT}.yml"

[[ -f "$ENV_FILE" ]] || { echo "Missing ${ENV_FILE}"; exit 1; }
[[ -f "$COMPOSE_FILE" ]] || { echo "Missing ${COMPOSE_FILE}"; exit 1; }

if [[ -n "$GIT_REF" ]]; then
  git fetch --all --tags --prune
  git checkout "$GIT_REF"
fi

docker compose --env-file "$ENV_FILE" -f "$COMPOSE_FILE" pull
docker compose --env-file "$ENV_FILE" -f "$COMPOSE_FILE" up -d --remove-orphans
docker compose --env-file "$ENV_FILE" -f "$COMPOSE_FILE" ps

echo "Release deployment completed for ${ENVIRONMENT}."
