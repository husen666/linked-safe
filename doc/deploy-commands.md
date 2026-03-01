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
