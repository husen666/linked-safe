# linked-safe

Linked Safe WordPress deployment repository for Tencent Cloud, with two environments:

- Staging: `test.linked-safe.com`
- Production: `linked-safe.com` (certificate pending)

## Directory Layout

- `deploy/docker-compose.staging.yml`: staging stack
- `deploy/docker-compose.prod.yml`: production stack
- `deploy/nginx/staging.conf`: staging HTTPS reverse proxy
- `deploy/nginx/prod.conf`: production HTTPS reverse proxy
- `deploy/scripts/first-deploy.sh`: first deployment (init site + SQL + cert)
- `deploy/scripts/release-deploy.sh`: normal release deployment
- `deploy/scripts/rollback.sh`: rollback to an older git ref
- `.github/workflows/deploy-staging.yml`: auto deploy on `develop`
- `.github/workflows/deploy-prod.yml`: manual deploy for production

## 1) First Deployment (Staging)

### Prepare server

1. Tencent Cloud CVM security group: open `22`, `80`, `443`.
2. Install Docker and Docker Compose plugin.
3. Clone repository:

```bash
git clone https://github.com/husen666/linked-safe.git /opt/linked-safe
cd /opt/linked-safe
```

### Prepare files

Upload these files manually to server path `bak/20260301/`:

- `bak/20260301/linkedsafe.com_20260301_115414.tar.gz`
- `bak/20260301/linkedsafe_com_2026-03-01_15-05-58_mysql_data_2o28n.sql.zip`
- `bak/20260301/test-linked-safe-com.zip`

Create env file:

```bash
cp deploy/.env.staging.example .env.staging
```

Edit `.env.staging` and set real DB passwords.

### Run first deployment

```bash
chmod +x deploy/scripts/*.sh
./deploy/scripts/first-deploy.sh staging
```

Verify:

```bash
docker compose --env-file .env.staging -f deploy/docker-compose.staging.yml ps
```

Then open `https://test.linked-safe.com`.

## 2) First Deployment (Production)

When production certificate is ready:

1. Upload production certificate zip manually to `bak/prod/` on server.
2. Copy and edit env file:

```bash
cp deploy/.env.prod.example .env.prod
```

3. Update certificate filenames in:
- `deploy/nginx/prod.conf`
- `.env.prod` (`CERT_ZIP`)

4. Run:

```bash
./deploy/scripts/first-deploy.sh prod
```

## 3) Normal Release Deployment

Run on server:

```bash
./deploy/scripts/release-deploy.sh staging
./deploy/scripts/release-deploy.sh prod
```

Deploy specific commit/tag:

```bash
./deploy/scripts/release-deploy.sh prod v1.0.0
```

## 4) Rollback

```bash
./deploy/scripts/rollback.sh staging <git_ref>
./deploy/scripts/rollback.sh prod <git_ref>
```

## 5) GitHub Actions Auto Deploy

### Staging workflow

`develop` branch push triggers `.github/workflows/deploy-staging.yml`.

Required repository secrets:

- `STAGING_HOST`
- `STAGING_USER`
- `STAGING_SSH_KEY`
- `STAGING_PORT`
- `STAGING_DEPLOY_DIR` (for example `/opt/linked-safe`)

### Production workflow

Manual trigger `.github/workflows/deploy-prod.yml` after approval.

Required repository secrets:

- `PROD_HOST`
- `PROD_USER`
- `PROD_SSH_KEY`
- `PROD_PORT`
- `PROD_DEPLOY_DIR` (for example `/opt/linked-safe`)

## Notes

- First deployment initializes MySQL from SQL backup once.
- Later releases do not overwrite the database volume.
- Never commit real secrets or certificate private keys.
- `bak/` is server-local and should not be committed to Git.