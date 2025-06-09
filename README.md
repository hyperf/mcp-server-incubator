# Hyperf MCP Server

[![Latest Stable Version](https://poser.pugx.org/hyperf/mcp-server-incubator/v/stable)](https://packagist.org/packages/hyperf/mcp-server-incubator)
[![Total Downloads](https://poser.pugx.org/hyperf/mcp-server-incubator/downloads)](https://packagist.org/packages/hyperf/mcp-server-incubator)
[![License](https://poser.pugx.org/hyperf/mcp-server-incubator/license)](https://packagist.org/packages/hyperf/mcp-server-incubator)

一个基于 Hyperf 框架的 Model Context Protocol (MCP) 服务器实现，提供了完整的工具、提示和资源管理功能，支持 Redis 会话管理和基于注解的配置。

## 特性

- 🚀 **高性能**: 基于 Hyperf 协程框架，支持高并发访问
- 🔧 **注解驱动**: 使用注解快速定义 MCP 工具、提示和资源
- 📦 **Redis 会话管理**: 内置 Redis 会话管理器，支持分布式部署
- 🎯 **类型安全**: 完整的类型提示和 JSON Schema 验证
- 🔒 **安全性**: 支持会话过期、元数据存储和访问控制
- 🎨 **灵活配置**: 支持分组管理和动态启用/禁用功能

## 安装

使用 Composer 安装：

```bash
composer require hyperf/mcp-server-incubator
```

## 快速开始

### 1. 配置服务

在你的 Hyperf 应用中发布配置：

```php
<?php
// config/autoload/dependencies.php
return [
    \Dtyq\PhpMcp\Server\Transports\Http\SessionManagerInterface::class => \Hyperf\McpServer\RedisSessionManager::class,
];
```

### 2. 定义工具

使用 `#[McpTool]` 注解定义 MCP 工具：

```php
<?php

use Hyperf\McpServer\Annotation\McpTool;

class CalculatorService
{
    #[McpTool(
        name: 'add_numbers',
        description: '计算两个数字的和',
        group: 'math'
    )]
    public function addNumbers(int $a, int $b): int
    {
        return $a + $b;
    }
    
    #[McpTool(
        name: 'multiply',
        description: '计算两个数字的乘积'
    )]
    public function multiply(float $x, float $y): float
    {
        return $x * $y;
    }
}
```

### 3. 定义提示

使用 `#[McpPrompt]` 注解定义智能提示：

```php
<?php

use Hyperf\McpServer\Annotation\McpPrompt;

class PromptService
{
    #[McpPrompt(
        name: 'code_review',
        description: '代码审查提示模板',
        group: 'development'
    )]
    public function codeReviewPrompt(string $language, string $code): string
    {
        return "请审查以下 {$language} 代码：\n\n```{$language}\n{$code}\n```\n\n请关注：\n- 代码质量\n- 潜在问题\n- 改进建议";
    }
}
```

### 4. 定义资源

使用 `#[McpResource]` 注解定义可访问的资源：

```php
<?php

use Hyperf\McpServer\Annotation\McpResource;

class DocumentService
{
    #[McpResource(
        name: 'api_docs',
        uri: 'mcp://docs/api',
        description: 'API 文档资源',
        mimeType: 'application/json'
    )]
    public function getApiDocs(): array
    {
        return [
            'title' => 'API Documentation',
            'version' => '1.0.0',
            'endpoints' => [
                // API 端点定义
            ]
        ];
    }
}
```

### 5. 启动服务器

创建控制器处理 MCP 请求：

```php
<?php

use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\RequestMapping;
use Hyperf\McpServer\McpServer;

#[Controller]
class McpController
{
    public function __construct(
        private McpServer $mcpServer
    ) {}
    
    #[RequestMapping(path: '/mcp', methods: ['GET', 'POST'])]
    public function handle()
    {
        return $this->mcpServer->handler();
    }
    
    #[RequestMapping(path: '/mcp/math', methods: ['GET', 'POST'])]
    public function handleMath()
    {
        // 只处理 math 分组的工具
        return $this->mcpServer->handler('math');
    }
}
```

## 高级配置

### Redis 会话管理器配置

```php
<?php
// config/autoload/redis.php
return [
    'default' => [
        'host' => env('REDIS_HOST', 'localhost'),
        'auth' => env('REDIS_AUTH', null),
        'port' => (int) env('REDIS_PORT', 6379),
        'db' => (int) env('REDIS_DB', 0),
        'pool' => [
            'min_connections' => 1,
            'max_connections' => 10,
            'connect_timeout' => 10.0,
            'wait_timeout' => 3.0,
            'heartbeat' => -1,
        ],
    ],
];
```

### 自定义会话 TTL

```php
<?php
// config/autoload/dependencies.php
return [
    \Hyperf\McpServer\RedisSessionManager::class => function () {
        return new \Hyperf\McpServer\RedisSessionManager(
            \Hyperf\Context\ApplicationContext::getContainer()->get(\Dtyq\PhpMcp\Shared\Kernel\Packer\PackerInterface::class),
            \Hyperf\Context\ApplicationContext::getContainer()->get(\Hyperf\Redis\RedisFactory::class),
            3600 // 1小时会话过期时间
        );
    },
];
```

## 注解参考

### #[McpTool]

| 参数 | 类型 | 描述 | 默认值 |
|------|------|------|--------|
| `name` | string | 工具名称 | 方法名 |
| `description` | string | 工具描述 | 空字符串 |
| `inputSchema` | array | 输入参数 Schema | 自动生成 |
| `group` | string | 分组名称 | 空字符串 |
| `enabled` | bool | 是否启用 | true |

### #[McpPrompt]

| 参数 | 类型 | 描述 | 默认值 |
|------|------|------|--------|
| `name` | string | 提示名称 | 方法名 |
| `description` | string | 提示描述 | 空字符串 |
| `arguments` | array | 提示参数 | 自动生成 |
| `group` | string | 分组名称 | 空字符串 |
| `enabled` | bool | 是否启用 | true |

### #[McpResource]

| 参数 | 类型 | 描述 | 默认值 |
|------|------|------|--------|
| `name` | string | 资源名称 | 方法名 |
| `uri` | string | 资源 URI | 自动生成 |
| `description` | string | 资源描述 | 空字符串 |
| `mimeType` | string\|null | MIME 类型 | null |
| `size` | int\|null | 资源大小 | null |
| `group` | string | 分组名称 | 空字符串 |
| `enabled` | bool | 是否启用 | true |
| `isTemplate` | bool | 是否为模板 | false |
| `uriTemplate` | array | URI 模板参数 | 空数组 |

## API 文档

### McpServer

主要的 MCP 服务器类，用于处理客户端请求。

#### 方法

- `handler(string $group = ''): ResponseInterface` - 处理 MCP 请求，可选择指定分组

### RedisSessionManager

基于 Redis 的会话管理器实现。

#### 方法

- `createSession(): string` - 创建新会话
- `isValidSession(string $sessionId): bool` - 检查会话是否有效
- `updateSessionActivity(string $sessionId): bool` - 更新会话活动时间
- `terminateSession(string $sessionId): bool` - 终止会话
- `getActiveSessions(): array` - 获取所有活动会话 ID
- `cleanupExpiredSessions(): int` - 清理过期会话
- `setSessionMetadata(string $sessionId, array $metadata): bool` - 设置会话元数据
- `getSessionMetadata(string $sessionId): ?array` - 获取会话元数据

### 项目结构

```
src/
├── ConfigProvider.php          # Hyperf 配置提供者
├── McpServer.php              # MCP 服务器主类
├── RedisSessionManager.php    # Redis 会话管理器
├── Annotation/                # 注解定义
│   ├── McpAnnotation.php      # 基础注解类
│   ├── McpPrompt.php          # 提示注解
│   ├── McpResource.php        # 资源注解
│   └── McpTool.php            # 工具注解
└── Collector/                 # 注解收集器
    └── McpCollector.php       # MCP 注解收集器
```

### 核心组件

#### ConfigProvider
自动配置 Hyperf 依赖注入容器，注册默认的会话管理器、认证器和序列化器。

#### McpServer
处理 MCP 协议请求的核心类，支持：
- 工具注册和执行
- 提示管理
- 资源访问
- 分组过滤

#### RedisSessionManager
基于 Redis 的会话管理实现，提供：
- 会话创建和验证
- 自动过期管理
- 元数据存储
- 活动会话跟踪

#### 注解系统
- `#[McpTool]`: 定义可调用的工具方法
- `#[McpPrompt]`: 定义智能提示模板
- `#[McpResource]`: 定义可访问的资源

#### McpCollector
自动收集和注册所有带有 MCP 注解的方法，支持分组管理和动态启用/禁用。

运行测试套件：

```bash
composer test
```

运行代码分析：

```bash
composer analyse
```

修复代码风格：

```bash
composer cs-fix
```

## 贡献

欢迎贡献代码！请遵循以下步骤：

1. Fork 这个项目
2. 创建你的特性分支 (`git checkout -b feature/AmazingFeature`)
3. 提交你的修改 (`git commit -m 'Add some AmazingFeature'`)
4. 推送到分支 (`git push origin feature/AmazingFeature`)
5. 打开一个 Pull Request

## 许可证

该项目基于 MIT 许可证开源。查看 [LICENSE](LICENSE) 文件了解更多详情。

## 相关链接

- [Hyperf 框架](https://hyperf.io)
- [Model Context Protocol](https://github.com/modelcontextprotocol)
- [Redis](https://redis.io)

## 更新日志

### v1.0.0

- 初始版本发布
- 支持基于注解的工具、提示和资源定义
- Redis 会话管理
- 分组管理功能
- 完整的类型安全支持
