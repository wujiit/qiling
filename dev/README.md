# 启灵主题开发目录

这个目录只存放本地开发、测试、审计、发布辅助相关文件，不属于用户安装主题时必须携带的运行时文件。

## 目录说明

- `tests/`：冒烟测试与测试说明。
- `tools/`：审计、发布打包、JS 语法检查等开发脚本。
- `phpstan/`：PHPStan 分析启动文件。
- `package.json`：Node 侧开发脚本入口。
- `composer.json`：PHP 侧静态检查工具入口。
- `phpcs.xml.dist`、`phpstan.neon.dist`：PHP 代码规范与静态分析配置。

## 常用命令

从主题根目录运行：

```bash
npm --prefix dev run check:js
npm --prefix dev run audit:customization
npm --prefix dev run test:smoke
```

或进入 `dev/` 后运行：

```bash
npm run check:js
npm run audit:customization
npm run test:smoke
```

静态分析：

```bash
composer run lint:php
composer run analyse:php
composer run analyse:php:baseline
```

`phpstan.neon.dist` 从 level 3 开始覆盖根模板、`templates/`、`template-parts/` 和 `inc/` 一线运行面；历史问题先进入 `phpstan-baseline.neon`，后续修复时逐步减少 baseline。

重建模块拆分 CSS：

```bash
npm --prefix dev run split:modules
```

## 发布说明

发布给用户的主题包会通过根目录 `.distignore` 排除整个 `dev/` 目录。

发布脚本默认把产物写到主题目录外的 `../dist/qiling/`，避免 `qiling/` 源码树里出现发布 zip、复制目录或 manifest。需要自定义输出目录时，可设置 `QILING_RELEASE_DIR`，但路径必须位于主题目录外。
