# Linked Safe 部署文档（腾讯云）

本文档用于指导 `linked-safe` 项目在腾讯云完成双环境部署：

- 测试环境：`test.linked-safe.com`
- 正式环境：`linked-safe.com`（证书后补）

发布规范请参考：`doc/release-flow.md`
命令速查请参考：`doc/deploy-commands.md`

当前仓库已具备以下部署资产：

- `deploy/docker-compose.staging.yml`
- `deploy/docker-compose.prod.yml`
- `deploy/nginx/staging.conf`
- `deploy/nginx/prod.conf`
- `deploy/scripts/first-deploy.sh`
- `deploy/scripts/release-deploy.sh`
- `deploy/scripts/rollback.sh`
- `.github/workflows/deploy-staging.yml`
- `.github/workflows/deploy-prod.yml`

## 1. 服务器准备

1. 准备腾讯云 CVM（建议至少 2C4G）。
2. 安全组放行端口：`22`、`80`、`443`。
3. 域名解析：
   - `test.linked-safe.com` -> 测试机公网 IP
   - `linked-safe.com` / `www.linked-safe.com` -> 正式机公网 IP（后续）
4. 安装 Docker（若未安装）：

```bash
curl -fsSL https://get.docker.com | sh
sudo systemctl enable --now docker
docker compose version
```

## 2. 拉取代码

```bash
git clone https://github.com/husen666/linked-safe.git /opt/linked-safe
cd /opt/linked-safe
```

## 3. 测试环境首次部署

### 3.1 手动上传备份包到服务器 `bak/20260301/`

将以下文件手动上传到服务器目录 `/opt/linked-safe/bak/20260301/`：

- `bak/20260301/linkedsafe.com_20260301_115414.tar.gz`
- `bak/20260301/linkedsafe_com_2026-03-01_15-05-58_mysql_data_2o28n.sql.zip`
- `bak/20260301/test-linked-safe-com.zip`（测试证书包）

### 3.2 准备环境变量

```bash
cp deploy/.env.staging.example .env.staging
```

编辑 `.env.staging`，至少修改：

- `MYSQL_PASSWORD`
- `MYSQL_ROOT_PASSWORD`
- `REDIS_PASSWORD`
- `WP_REDIS_PASSWORD`（建议与 `REDIS_PASSWORD` 一致）

### 3.3 执行首次部署脚本

```bash
chmod +x deploy/scripts/*.sh
./deploy/scripts/first-deploy.sh staging
```

### 3.4 验证

```bash
docker compose --env-file .env.staging -f deploy/docker-compose.staging.yml ps
```

浏览器访问：`https://test.linked-safe.com`

## 4. 正式环境首次部署（证书到位后）

### 4.1 上传正式证书包并配置

1. 将正式证书 zip 手动上传到服务器（建议路径：`/opt/linked-safe/bak/prod/`）。
2. 复制配置：

```bash
cp deploy/.env.prod.example .env.prod
```

3. 更新 `.env.prod`：
   - `CERT_ZIP=bak/prod/你的正式证书zip文件名`
   - `MYSQL_PASSWORD`
   - `MYSQL_ROOT_PASSWORD`
   - `REDIS_PASSWORD`
   - `WP_REDIS_PASSWORD`（建议与 `REDIS_PASSWORD` 一致）
4. 如正式证书文件名与默认不一致，修改：
   - `deploy/nginx/prod.conf` 中 `ssl_certificate`、`ssl_certificate_key` 路径

### 4.2 执行部署

```bash
./deploy/scripts/first-deploy.sh prod
```

## 5. 后续发布（非首次）

### 5.1 服务器手动发布

```bash
# 测试环境
./deploy/scripts/release-deploy.sh staging

# 正式环境
./deploy/scripts/release-deploy.sh prod
```

指定版本（tag/commit）发布：

```bash
./deploy/scripts/release-deploy.sh prod v1.0.0
```

### 5.2 回滚

```bash
# 测试环境回滚
./deploy/scripts/rollback.sh staging <git_ref>

# 正式环境回滚
./deploy/scripts/rollback.sh prod <git_ref>
```

## 6. 自动部署（GitHub Actions）

### 6.1 测试环境自动部署

触发条件：推送到 `develop` 分支。

对应 workflow：`.github/workflows/deploy-staging.yml`

需要在 GitHub 仓库 Secrets 中配置：

- `STAGING_HOST`
- `STAGING_USER`
- `STAGING_SSH_KEY`
- `STAGING_PORT`
- `STAGING_DEPLOY_DIR`（例如 `/opt/linked-safe`）

### 6.2 正式环境自动部署

触发条件：手动执行 workflow。

对应 workflow：`.github/workflows/deploy-prod.yml`

需要配置 Secrets：

- `PROD_HOST`
- `PROD_USER`
- `PROD_SSH_KEY`
- `PROD_PORT`
- `PROD_DEPLOY_DIR`（例如 `/opt/linked-safe`）

## 7. 运维常用命令

```bash
# 查看状态（测试）
docker compose --env-file .env.staging -f deploy/docker-compose.staging.yml ps

# 查看日志（测试）
docker compose --env-file .env.staging -f deploy/docker-compose.staging.yml logs -f

# 重启（测试）
docker compose --env-file .env.staging -f deploy/docker-compose.staging.yml restart

# 停止（测试）
docker compose --env-file .env.staging -f deploy/docker-compose.staging.yml down
```

## 8. 注意事项

1. 首次部署会导入 SQL 初始化数据库；后续发布不会覆盖数据库卷。
2. 不要提交以下文件到仓库：
   - `.env.staging`、`.env.prod`
   - 证书私钥
3. 备份压缩包与证书统一放在服务器本地 `bak/` 目录，不提交 Git。
4. 测试与正式建议分开服务器，避免互相影响。

## 9. 4C8G 推荐性能配置

仓库已内置中型站点优化参数：

- PHP: `deploy/php/custom.ini`
- MySQL: `deploy/mysql/my.cnf`
- Nginx: `deploy/nginx/performance.conf`
- Redis: compose 内置 `redis:7-alpine`（对象缓存）

在服务器更新代码后执行：

```bash
cd /opt/linked-safe
git pull
docker compose --env-file .env.staging -f deploy/docker-compose.staging.yml up -d --force-recreate
```

生产环境同理：

```bash
docker compose --env-file .env.prod -f deploy/docker-compose.prod.yml up -d --force-recreate
```

验证配置生效：

```bash
# PHP
docker compose --env-file .env.staging -f deploy/docker-compose.staging.yml exec -T wordpress php -i | grep -E "memory_limit|max_execution_time|opcache.memory_consumption"

# MySQL
docker compose --env-file .env.staging -f deploy/docker-compose.staging.yml exec -T db sh -c 'mysql -u"$MYSQL_USER" -p"$MYSQL_PASSWORD" -e "SHOW VARIABLES LIKE '\''innodb_buffer_pool_size'\''; SHOW VARIABLES LIKE '\''max_connections'\'';"'

# Nginx
docker compose --env-file .env.staging -f deploy/docker-compose.staging.yml exec -T nginx nginx -T | grep -E "gzip on|keepalive_timeout|proxy_read_timeout"
```

启用 WordPress 对象缓存（后台）：

1. 安装并启用插件 `Redis Object Cache`（若未安装）。
2. WordPress 后台 -> `设置` -> `Redis` -> 点击 `Enable Object Cache`。

验证 Redis 连接：

```bash
docker compose --env-file .env.staging -f deploy/docker-compose.staging.yml exec -T redis sh -c 'redis-cli -a "$REDIS_PASSWORD" ping'
```

验证 Redis 命中情况（执行两次对比）：

```bash
# 第1次：记录基线
docker compose --env-file .env.staging -f deploy/docker-compose.staging.yml exec -T redis sh -c 'redis-cli -a "$REDIS_PASSWORD" info stats | egrep "keyspace_hits|keyspace_misses|total_commands_processed"'

# 打开站点前后台各几次后，再执行第2次
docker compose --env-file .env.staging -f deploy/docker-compose.staging.yml exec -T redis sh -c 'redis-cli -a "$REDIS_PASSWORD" info stats | egrep "keyspace_hits|keyspace_misses|total_commands_processed"'
```

查看 Redis 当前缓存键数量：

```bash
docker compose --env-file .env.staging -f deploy/docker-compose.staging.yml exec -T redis sh -c 'redis-cli -a "$REDIS_PASSWORD" dbsize'
```

服务器本地放弃修改并强制拉取最新代码：

```bash
cd /opt/linked-safe
git fetch origin
git reset --hard origin/main
git clean -fd
git pull
```
