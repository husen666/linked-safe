#!/usr/bin/env bash
set -euo pipefail

if [[ $# -lt 2 ]]; then
  echo "Usage: $0 <staging|prod> <git_ref>"
  exit 1
fi

ENVIRONMENT="$1"
TARGET_REF="$2"

if [[ "$ENVIRONMENT" != "staging" && "$ENVIRONMENT" != "prod" ]]; then
  echo "ENVIRONMENT must be staging or prod"
  exit 1
fi

REPO_ROOT="$(cd "$(dirname "${BASH_SOURCE[0]}")/../.." && pwd)"
cd "$REPO_ROOT"

git fetch --all --tags --prune
git checkout "$TARGET_REF"

"$REPO_ROOT/deploy/scripts/release-deploy.sh" "$ENVIRONMENT"

echo "Rollback completed for ${ENVIRONMENT} to ${TARGET_REF}."
