#!/usr/bin/env bash
set -euo pipefail

# WordPress site 瘦身打包：排除 uploads、静态导出、常见备份与缓存目录。
# 在仓库根目录执行（与服务器上 /opt/linked-safe 布局一致）。

REPO_ROOT="$(cd "$(dirname "${BASH_SOURCE[0]}")/../.." && pwd)"
cd "$REPO_ROOT"

ENV_NAME="${1:-staging}"
if [[ "$ENV_NAME" != "staging" && "$ENV_NAME" != "prod" ]]; then
  echo "Usage: $0 [staging|prod]" >&2
  exit 1
fi

OUT="/tmp/linkedsafe-${ENV_NAME}-site-no-uploads-$(date +%Y%m%d).tar.gz"

tar -czf "$OUT" \
  --exclude='site/wp-content/uploads' \
  --exclude='site/simply-static-*.zip' \
  --exclude='site/html' \
  --exclude='site/wp-content/cache' \
  --exclude='site/wp-content/upgrade' \
  --exclude='site/wp-content/backups' \
  --exclude='site/wp-content/ai1wm-backups' \
  --exclude='site/wp-content/updraft' \
  site

echo "$OUT"
