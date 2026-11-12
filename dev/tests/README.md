# 启灵主题测试目录说明

这套测试不是给终端用户运行的功能，而是跟随主题仓库一起维护的质量护栏。

当前第一阶段测试策略：

1. `dev/tests/smoke`
   - 轻量级脚本冒烟测试
   - 主要验证高风险主链路的文件存在、入口连接、关键 action / service / payload 协议没有断
2. `dev/tools/check-js-syntax.mjs`
   - JavaScript 语法检查
3. `dev/tools/audit-customization-readiness.mjs`
   - 装修能力封板自检
   - 验证常用模块全局设计接入、页面级覆盖网络、后台普通用户入口没有断
4. Composer 工具链
   - PHP 语法检查
   - PHPCS
   - PHPStan

## 当前 smoke 覆盖范围

- AI 装修
- 前台 Builder
- 页面包导入导出
- 全局设计与页面级覆盖合同
- 常用模块全局设计接入合同

## 运行方式

完整 Node 侧检查：

```bash
cd dev
npm run check
```

单独跑装修能力自检：

```bash
cd dev
npm run audit:customization
```

单独跑冒烟测试：

```bash
cd dev
npm run test:smoke
```

如果只想跑 JavaScript 语法检查：

```bash
cd dev
npm run check:js
```

重建模块拆分 CSS：

```bash
cd dev
npm run split:modules
```

## 为什么测试放在主题仓库里

因为回归验证本身就是主题项目的一部分：

- 代码改了，测试也要一起更新
- CI 要能自动跑
- 换机器或换人维护时，验证方式不能丢

所以测试应该跟着主题仓库走，而不是只存在于人工口头流程里。
