# Linked Safe 部署命令总清单

本文档只放命令，按场景直接复制执行。

## 1) 服务器初始化

```bash
curl -fsSL https://get.docker.com | sh
sudo systemctl enable --now docker
docker compose version
```

## 2) 拉取代码

```bash
git clone https://github.com/husen666/linked-safe.git /opt/linked-safe
cd /opt/linked-safe
```

## 3) 测试环境首次部署（staging）

```bash
cd /opt/linked-safe
cp deploy/.env.staging.example .env.staging
chmod +x deploy/scripts/*.sh
./deploy/scripts/first-deploy.sh staging
```

## 4) 正式环境首次部署（prod）

```bash
cd /opt/linked-safe
cp deploy/.env.prod.example .env.prod
chmod +x deploy/scripts/*.sh
./deploy/scripts/first-deploy.sh prod
```

## 5) 后续发布（非首次）

```bash
cd /opt/linked-safe
./deploy/scripts/release-deploy.sh staging
./deploy/scripts/release-deploy.sh prod
```

指定版本发布：

```bash
cd /opt/linked-safe
./deploy/scripts/release-deploy.sh prod v1.0.0
```

## 6) 回滚

```bash
cd /opt/linked-safe
./deploy/scripts/rollback.sh staging <git_ref>
./deploy/scripts/rollback.sh prod <git_ref>
```

## 7) 服务状态与日志

```bash
cd /opt/linked-safe
docker compose --env-file .env.staging -f deploy/docker-compose.staging.yml ps
docker compose --env-file .env.staging -f deploy/docker-compose.staging.yml logs -f
```

```bash
cd /opt/linked-safe
docker compose --env-file .env.prod -f deploy/docker-compose.prod.yml ps
docker compose --env-file .env.prod -f deploy/docker-compose.prod.yml logs -f
```

## 8) 重启 / 停止

```bash
cd /opt/linked-safe
docker compose --env-file .env.staging -f deploy/docker-compose.staging.yml restart
docker compose --env-file .env.staging -f deploy/docker-compose.staging.yml down
```

```bash
cd /opt/linked-safe
docker compose --env-file .env.prod -f deploy/docker-compose.prod.yml restart
docker compose --env-file .env.prod -f deploy/docker-compose.prod.yml down
```

## 9) 4C8G 性能配置生效验证

```bash
# PHP
docker compose --env-file .env.staging -f deploy/docker-compose.staging.yml exec -T wordpress php -i | grep -E "memory_limit|max_execution_time|opcache.memory_consumption"

# MySQL
docker compose --env-file .env.staging -f deploy/docker-compose.staging.yml exec -T db sh -c 'mysql -u"$MYSQL_USER" -p"$MYSQL_PASSWORD" -e "SHOW VARIABLES LIKE '\''innodb_buffer_pool_size'\''; SHOW VARIABLES LIKE '\''max_connections'\'';"'

# Nginx
docker compose --env-file .env.staging -f deploy/docker-compose.staging.yml exec -T nginx nginx -T | grep -E "gzip on|proxy_read_timeout"
```

## 10) Redis 对象缓存

```bash
# Redis 容器连通性
docker compose --env-file .env.staging -f deploy/docker-compose.staging.yml exec -T redis sh -c 'redis-cli -a "$REDIS_PASSWORD" ping'

# 命中率（执行两次对比）
docker compose --env-file .env.staging -f deploy/docker-compose.staging.yml exec -T redis sh -c 'redis-cli -a "$REDIS_PASSWORD" info stats | egrep "keyspace_hits|keyspace_misses|total_commands_processed"'

# 缓存键数量
docker compose --env-file .env.staging -f deploy/docker-compose.staging.yml exec -T redis sh -c 'redis-cli -a "$REDIS_PASSWORD" dbsize'
```

## 11) 常见故障快速命令

Nginx 重启失败排查：

```bash
cd /opt/linked-safe
docker compose --env-file .env.staging -f deploy/docker-compose.staging.yml logs --tail 200 nginx
docker compose --env-file .env.staging -f deploy/docker-compose.staging.yml run --rm --no-deps nginx nginx -t
```

数据库是否已导入：

```bash
cd /opt/linked-safe
docker compose --env-file .env.staging -f deploy/docker-compose.staging.yml exec -T db sh -lc 'MYSQL_PWD="$MYSQL_PASSWORD" mysql -u"$MYSQL_USER" -D "$MYSQL_DATABASE" -N -e "SELECT COUNT(*) FROM information_schema.tables WHERE table_schema=DATABASE();"'
```

强制重建 staging：

```bash
cd /opt/linked-safe
docker compose --env-file .env.staging -f deploy/docker-compose.staging.yml down -v
./deploy/scripts/first-deploy.sh staging
```

## 12) 放弃服务器本地修改并拉最新代码

```bash
cd /opt/linked-safe
git fetch origin
git reset --hard origin/main
git clean -fd
git pull
```

## 13) 备份网站文件与数据库

在服务器上执行，默认输出到 `/tmp`，文件名带日期。需要时用 `scp` 拉回本机。若未加入 `docker` 组，在 `docker` 前加 `sudo`。数据库密码从对应 `.env` 读取，勿把真实密码写进仓库或提交到 Git。

### 13.1 测试环境（staging）

**网站（WordPress 整站，即仓库下 `site/` 目录）：**

```bash
cd /opt/linked-safe
tar -czf /tmp/linkedsafe-staging-site-$(date +%Y%m%d).tar.gz site
```

**数据库（全库）：**

```bash
cd /opt/linked-safe
source .env.staging
docker exec -i linkedsafe-staging-db \
  mysqldump -uroot -p"${MYSQL_ROOT_PASSWORD}" --single-transaction --quick --all-databases \
  > /tmp/linkedsafe-staging-db-$(date +%Y%m%d).sql
```

**数据库（仅业务库，`MYSQL_DATABASE`）：**

```bash
cd /opt/linked-safe
source .env.staging
docker exec -i linkedsafe-staging-db \
  mysqldump -uroot -p"${MYSQL_ROOT_PASSWORD}" --single-transaction --quick "${MYSQL_DATABASE}" \
  > /tmp/linkedsafe-staging-db-$(date +%Y%m%d).sql
```

**交互输入 root 密码（不落盘、不进历史）：**

```bash
docker exec -i linkedsafe-staging-db \
  mysqldump -uroot -p --single-transaction --quick --all-databases \
  > /tmp/linkedsafe-staging-db-$(date +%Y%m%d).sql
```

### 13.2 正式环境（prod）

**网站：**

```bash
cd /opt/linked-safe
tar -czf /tmp/linkedsafe-prod-site-$(date +%Y%m%d).tar.gz site
```

**数据库（全库）：**

```bash
cd /opt/linked-safe
source .env.prod
docker exec -i linkedsafe-prod-db \
  mysqldump -uroot -p"${MYSQL_ROOT_PASSWORD}" --single-transaction --quick --all-databases \
  > /tmp/linkedsafe-prod-db-$(date +%Y%m%d).sql
```

**数据库（仅业务库）：**

```bash
cd /opt/linked-safe
source .env.prod
docker exec -i linkedsafe-prod-db \
  mysqldump -uroot -p"${MYSQL_ROOT_PASSWORD}" --single-transaction --quick "${MYSQL_DATABASE}" \
  > /tmp/linkedsafe-prod-db-$(date +%Y%m%d).sql
```

### 13.3 命令行直接写用户名、密码（不读 .env）

把下面命令里的 **用户名**、**密码**、**库名** 换成实际值。密码建议用**单引号**包起来，避免特殊字符被 shell 解析。

**注意：** 密码会出现在终端历史记录（`~/.bash_history`）和短时进程列表中，用完后可执行 `history -d` 删掉该条，或优先使用上文「交互输入 `-p`」方式。勿将真实密码写入本仓库或提交 Git，仅把下面命令里的 `你的root密码` 换成服务器上的值。

**测试环境单行（全库导出到 `/tmp`，与常用写法等价）：**

```bash
docker exec -i linkedsafe-staging-db mysqldump -uroot -p'你的root密码' --all-databases --single-transaction --quick > /tmp/linkedsafe-staging-$(date +%Y%m%d).sql
```

说明：重定向保存文件时用 `-i` 即可，不要用 `-t`（分配 TTY 可能引起异常）。用户名写成 `-uroot` 或 `-u root` 均可。

**测试库容器 `linkedsafe-staging-db` — 全库（多行排版）：**

```bash
docker exec -i linkedsafe-staging-db \
  mysqldump -uroot -p'你的root密码' --single-transaction --quick --all-databases \
  > /tmp/linkedsafe-staging-db-$(date +%Y%m%d).sql
```

**测试库 — 指定业务用户与库名（示例与 `.env.staging.example` 一致时可照抄用户名/库名）：**

```bash
docker exec -i linkedsafe-staging-db \
  mysqldump -ulinkedsafe_com -p'你的数据库用户密码' --single-transaction --quick linkedsafe_com \
  > /tmp/linkedsafe-staging-db-$(date +%Y%m%d).sql
```

**正式库容器 `linkedsafe-prod-db` — 全库：**

```bash
docker exec -i linkedsafe-prod-db \
  mysqldump -uroot -p'你的root密码' --single-transaction --quick --all-databases \
  > /tmp/linkedsafe-prod-db-$(date +%Y%m%d).sql
```

**正式库 — 指定用户与库名（用户名、库名以 `.env.prod` 为准）：**

```bash
docker exec -i linkedsafe-prod-db \
  mysqldump -u你的用户名 -p'你的密码' --single-transaction --quick 你的库名 \
  > /tmp/linkedsafe-prod-db-$(date +%Y%m%d).sql
```

### 13.4 网站备份排除 `wp-content/uploads`（媒体库）

不含上传的图片与附件，包体积会小很多，适合只迁代码与主题插件。**媒体需另备**（例如单独打包 `uploads`、对象存储或再拷一份）。

**推荐：仓库脚本（排除 `uploads`、Simply Static 包与 `html/`、常见备份与缓存目录），输出路径打印在最后一行：**

```bash
cd /opt/linked-safe
chmod +x deploy/scripts/backup-site-slim.sh
./deploy/scripts/backup-site-slim.sh staging
# 正式环境：./deploy/scripts/backup-site-slim.sh prod
```

**仅手工排除 `uploads`（测试 / 正式）：**

```bash
cd /opt/linked-safe
tar -czf /tmp/linkedsafe-staging-site-no-uploads-$(date +%Y%m%d).tar.gz \
  --exclude='site/wp-content/uploads' \
  site
```

```bash
cd /opt/linked-safe
tar -czf /tmp/linkedsafe-prod-site-no-uploads-$(date +%Y%m%d).tar.gz \
  --exclude='site/wp-content/uploads' \
  site
```

## 14) 本地部署（local）

从线上备份（site 打包 + SQL 导出）在本机 Docker 启动完整站点。容器名 `linkedsafe-*`，MySQL 映射到本机 **3308** 端口，站点文件映射到 `site/` 方便直接编辑。通过 hosts 绑定 `test.linked-safe.com` 到 `127.0.0.1`，浏览器以 HTTPS 访问。

### 14.1 前置条件

- 本机已安装 Docker Desktop（`docker compose version` 可执行）
- 已安装 [mkcert](https://github.com/FiloSottile/mkcert)（生成本地可信证书）
- 已从服务器拷回：
  - `linkedsafe-staging-20260407.sql`
  - `linkedsafe-staging-site-no-uploads-20260408.tar.gz`

### 14.2 hosts 绑定

添加：

```bash
sudo sh -c 'echo "127.0.0.1 test.linked-safe.com" >> /etc/hosts'
```

删除（不再需要本地访问时）：

```bash
sudo sed -i '' '/127.0.0.1 test.linked-safe.com/d' /etc/hosts
```

Linux 上若 `sed -i ''` 报错，可改用：`sudo sed -i '/127.0.0.1 test.linked-safe.com/d' /etc/hosts`

### 14.3 生成本地 SSL 证书（mkcert）

```bash
mkcert -install
cd /path/to/linked-safe
mkdir -p ssl/local
mkcert -cert-file ssl/local/cert.pem -key-file ssl/local/key.pem test.linked-safe.com
```

### 14.4 解压站点文件 + 准备 SQL

```bash
cd /path/to/linked-safe
rm -rf site
tar -xzf /path/to/linkedsafe-staging-site-no-uploads-20260408.tar.gz
mkdir -p mysql-init
cp /path/to/linkedsafe-staging-20260407.sql mysql-init/01-init.sql
```

### 14.5 准备环境变量

```bash
cd /path/to/linked-safe
cp deploy/.env.local.example .env.local
```

编辑 `.env.local`，修改 `MYSQL_PASSWORD`、`MYSQL_ROOT_PASSWORD`、`REDIS_PASSWORD` 等密码（与线上一致或自定义均可）。确保 `WORDPRESS_DB_PASSWORD` 与 `MYSQL_PASSWORD` 一致。

### 14.6 修改 wp-config.php

```bash
cd /path/to/linked-safe
sed -i '' "s/define( 'DB_HOST', 'localhost' );/define( 'DB_HOST', 'db:3306' );/" site/wp-config.php
```

若 `wp-config.php` 已在 staging 部署时改过 `DB_HOST` 为 `db:3306`，此步可跳过。

### 14.7 启动

```bash
cd /path/to/linked-safe
docker compose --env-file .env.local -f deploy/docker-compose.local.yml up -d
docker compose --env-file .env.local -f deploy/docker-compose.local.yml ps
```

### 14.8 验证

浏览器打开 `https://test.linked-safe.com`。

本机连接 MySQL：

```bash
mysql -h 127.0.0.1 -P 3308 -uroot -p
```

### 14.9 停止 / 重建

```bash
cd /path/to/linked-safe
docker compose --env-file .env.local -f deploy/docker-compose.local.yml down
# 清空数据卷后重建（重新导入 SQL）：
docker compose --env-file .env.local -f deploy/docker-compose.local.yml down -v
docker compose --env-file .env.local -f deploy/docker-compose.local.yml up -d
```
