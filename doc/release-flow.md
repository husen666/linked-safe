# Linked Safe 发布流程规范

本文档定义 `linked-safe` 的 Git 发布与环境管理流程，适用于：

- 测试环境：`test.linked-safe.com`
- 正式环境：`linked-safe.com`

## 1. 分支与环境映射

- `main`：正式环境基线分支
- `develop`：测试环境集成分支
- `feature/*`：功能开发分支
- `hotfix/*`：线上紧急修复分支

映射关系：

- `develop` -> 自动部署测试环境
- `main` -> 手动触发正式部署

## 2. 日常开发流程

1. 从 `develop` 拉取最新代码并创建功能分支：

```bash
git checkout develop
git pull
git checkout -b feature/<name>
```

2. 开发完成后提交并推送：

```bash
git add .
git commit -m "feat: <description>"
git push origin feature/<name>
```

3. 发起 PR 合并到 `develop`，完成代码评审与测试。

## 3. 测试环境发布流程

1. PR 合并到 `develop`。
2. GitHub Actions 自动触发 `.github/workflows/deploy-staging.yml`。
3. 部署完成后在测试域名进行回归验证：
   - 首页、登录、下单流程
   - 后台核心功能
   - 关键插件与支付链路（如适用）

如果测试不通过：

- 直接在 `develop` 修复后再次触发自动部署
- 或用回滚脚本回退测试环境

## 4. 正式环境发布流程

推荐按发布窗口执行：

1. 从 `develop` 创建发布 PR 到 `main`。
2. 完成最终评审并合并。
3. 在 GitHub 手动触发 `.github/workflows/deploy-prod.yml`。
4. 可选择指定 `tag`/`commit` 发布。

发布后验证：

- 网站可访问（HTTPS、证书正常）
- 核心页面状态正常（首页、商品页、结账）
- 应用日志无明显错误

## 5. 紧急修复流程（Hotfix）

1. 从 `main` 切出分支：

```bash
git checkout main
git pull
git checkout -b hotfix/<name>
```

2. 修复后提交并合并到 `main`，立刻发布生产。
3. 将同样修复合并回 `develop`，保持分支一致。

## 6. 版本与 Tag 规范

建议语义化版本：

- `v1.0.0`：大版本
- `v1.1.0`：功能版本
- `v1.1.1`：修复版本

发布正式环境前建议打 tag：

```bash
git checkout main
git pull
git tag v1.0.0
git push origin v1.0.0
```

## 7. 回滚规范

当正式发布异常时：

1. 确认上一个稳定版本 `git_ref`（tag 或 commit）。
2. 在服务器执行：

```bash
./deploy/scripts/rollback.sh prod <git_ref>
```

3. 验证恢复状态并记录故障原因。

## 8. 发布前检查清单

- 变更已通过代码评审
- 测试环境验证通过
- 数据库变更已评估（是否影响回滚）
- 已确认备份策略可用
- 发布窗口与回滚负责人明确

## 9. 权限与安全要求

- 生产发布仅允许指定维护人员触发
- GitHub Secrets 不得明文存储在仓库
- 证书私钥和 `.env.*` 文件禁止提交
- 生产操作必须可追溯（PR、workflow、发布记录）
