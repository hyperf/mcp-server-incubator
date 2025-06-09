# Hyperf MCP Server

[![Latest Stable Version](https://poser.pugx.org/hyperf/mcp-server-incubator/v/stable)](https://packagist.org/packages/hyperf/mcp-server-incubator)
[![Total Downloads](https://poser.pugx.org/hyperf/mcp-server-incubator/downloads)](https://packagist.org/packages/hyperf/mcp-server-incubator)
[![License](https://poser.pugx.org/hyperf/mcp-server-incubator/license)](https://packagist.org/packages/hyperf/mcp-server-incubator)

一个基于 Hyperf 框架的 Model Context Protocol (MCP) 服务器实现，提供了完整的工具、提示和资源管理功能，支持 Redis 会话管理和基于注解的配置。基于 [dtyq/php-mcp](https://github.com/dtyq/php-mcp) 核心库构建。

## 特性

- 🚀 **高性能**: 基于 Hyperf 协程框架，支持高并发访问
- 🔧 **注解驱动**: 使用注解快速定义 MCP 工具、提示和资源
- 📦 **Redis 会话管理**: 内置 Redis 会话管理器，支持分布式部署
- 🎯 **类型安全**: 完整的类型提示和 JSON Schema 验证
- 🔒 **安全性**: 支持会话过期、元数据存储和访问控制
- 🏗️ **多服务器架构**: 支持多个 MCP 服务器实例，便于功能分组和管理
- 🎨 **灵活配置**: 支持服务器分组管理和动态启用/禁用功能

## 安装

### 系统要求

- PHP 8.1 或更高版本
- Hyperf 3.0 或更高版本
- Redis 扩展
- Redis 服务器

### 使用 Composer 安装

```bash
composer require hyperf/mcp-server-incubator
```

### 发布配置文件

如果你需要自定义配置，可以发布配置文件：

```bash
php bin/hyperf.php vendor:publish hyperf/mcp-server-incubator
```

## 快速开始

### 1. 配置服务

在你的 Hyperf 应用中发布配置：

```php
<?php
// config/autoload/dependencies.php
return [
    \Dtyq\PhpMcp\Server\Transports\Http\SessionManagerInterface::class => \Hyperf\McpServer\RedisSessionManager::class,
    \Dtyq\PhpMcp\Shared\Auth\AuthenticatorInterface::class => \Dtyq\PhpMcp\Shared\Auth\NullAuthenticator::class,
    \Dtyq\PhpMcp\Shared\Kernel\Packer\PackerInterface::class => \Dtyq\PhpMcp\Shared\Kernel\Packer\OpisClosurePacker::class,
];
```

### 2. 定义工具

使用 `#[Tool]` 注解定义 MCP 工具：

```php
<?php

use Hyperf\McpServer\Annotation\Tool;

class CalculatorService
{
    #[Tool(
        name: 'add_numbers',
        description: '计算两个数字的和',
        server: 'math'
    )]
    public function addNumbers(int $a, int $b): int
    {
        return $a + $b;
    }
    
    #[Tool(
        name: 'multiply',
        description: '计算两个数字的乘积',
        server: 'math'
    )]
    public function multiply(float $x, float $y): float
    {
        return $x * $y;
    }
}
```

### 3. 定义提示

使用 `#[Prompt]` 注解定义智能提示：

```php
<?php

use Hyperf\McpServer\Annotation\Prompt;

class PromptService
{
    #[Prompt(
        name: 'code_review',
        description: '代码审查提示模板',
        server: 'development'
    )]
    public function codeReviewPrompt(string $language, string $code): string
    {
        return "请审查以下 {$language} 代码：\n\n```{$language}\n{$code}\n```\n\n请关注：\n- 代码质量\n- 潜在问题\n- 改进建议";
    }
}
```

### 4. 定义资源

使用 `#[Resource]` 注解定义可访问的资源：

```php
<?php

use Hyperf\McpServer\Annotation\Resource;

class DocumentService
{
    #[Resource(
        name: 'api_docs',
        uri: 'mcp://docs/api',
        description: 'API 文档资源',
        mimeType: 'application/json',
        server: 'docs'
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
use Hyperf\McpServer\Server;

#[Controller]
class McpController
{
    public function __construct(
        private Server $server
    ) {}
    
    #[RequestMapping(path: '/mcp', methods: ['GET', 'POST'])]
    public function handle()
    {
        return $this->server->handle();
    }
    
    #[RequestMapping(path: '/mcp/math', methods: ['GET', 'POST'])]
    public function handleMath()
    {
        // 只处理 math 服务器的工具
        return $this->server->handle('math');
    }
}
```

## 完整示例

这里是一个完整的服务示例，展示如何使用所有注解功能：

```php
<?php

use Hyperf\McpServer\Annotation\Tool;
use Hyperf\McpServer\Annotation\Prompt;
use Hyperf\McpServer\Annotation\Resource;

class ComprehensiveService
{
    // 数学工具 - 使用 math 服务器
    #[Tool(
        name: 'math_add',
        description: 'Add two numbers',
        server: 'math'
    )]
    public function addNumbers(int $a, int $b): int
    {
        return $a + $b;
    }

    #[Tool(
        name: 'math_multiply',
        description: 'Multiply two numbers',
        server: 'math'
    )]
    public function multiplyNumbers(float $x, float $y): float
    {
        return $x * $y;
    }

    // 文本处理工具 - 使用默认服务器
    #[Tool(
        name: 'text_processor',
        description: 'Process text input with various transformations',
        server: 'text'
    )]
    public function processText(string $input, string $operation = 'upper'): string
    {
        return match($operation) {
            'upper' => strtoupper($input),
            'lower' => strtolower($input),
            'reverse' => strrev($input),
            default => $input
        };
    }

    // 代码审查提示 - 使用 development 服务器
    #[Prompt(
        name: 'code_review',
        description: 'Generate code review prompt',
        server: 'development'
    )]
    public function codeReviewPrompt(string $language, string $code): string
    {
        return "请审查以下 {$language} 代码：\n\n```{$language}\n{$code}\n```\n\n请关注：\n- 代码质量\n- 潜在问题\n- 改进建议";
    }

    // API 文档资源 - 使用 docs 服务器
    #[Resource(
        name: 'api_docs',
        uri: 'mcp://docs/api',
        description: 'API documentation resource',
        mimeType: 'application/json',
        server: 'docs'
    )]
    public function getApiDocs(): array
    {
        return [
            'title' => 'API Documentation',
            'version' => '1.0.0',
            'endpoints' => [
                [
                    'path' => '/api/tools',
                    'method' => 'GET',
                    'description' => 'List all available tools'
                ],
                [
                    'path' => '/api/prompts',
                    'method' => 'GET',
                    'description' => 'List all available prompts'
                ]
            ]
        ];
    }

    // 可以禁用的工具示例
    #[Tool(
        name: 'experimental_feature',
        description: 'An experimental feature that can be disabled',
        enabled: false
    )]
    public function experimentalFeature(): string
    {
        return 'This feature is currently disabled';
    }
}
```

## 高级配置

### Redis 配置

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

### 会话 TTL 和生命周期管理

RedisSessionManager 提供了完整的会话生命周期管理：

```php
<?php
// 创建自定义 TTL 的会话管理器
// config/autoload/dependencies.php
return [
    \Hyperf\McpServer\RedisSessionManager::class => function ($container) {
        return new \Hyperf\McpServer\RedisSessionManager(
            $container->get(\Dtyq\PhpMcp\Shared\Kernel\Packer\PackerInterface::class),
            $container->get(\Hyperf\Redis\RedisFactory::class),
            3600 // 1小时会话过期时间
        );
    },
];
```

#### 会话监控和管理

```php
<?php
// 获取会话详细信息
$sessionDetails = $sessionManager->getSessionDetails($sessionId);
// 返回: ['created_at' => 1234567890, 'last_activity' => 1234567890, 'ttl' => 3600]

// 获取活动会话总数
$activeCount = $sessionManager->getSessionCount();

// 手动清理过期会话
$cleanedCount = $sessionManager->cleanupExpiredSessions();
```

## 注解参考

### #[Tool]

| 参数 | 类型 | 描述 | 默认值 |
|------|------|------|--------|
| `name` | string | 工具名称 | 方法名 |
| `description` | string | 工具描述 | 空字符串 |
| `inputSchema` | array | 输入参数 Schema | 自动生成 |
| `server` | string | 服务器名称 | 'default' |
| `enabled` | bool | 是否启用 | true |

### #[Prompt]

| 参数 | 类型 | 描述 | 默认值 |
|------|------|------|--------|
| `name` | string | 提示名称 | 方法名 |
| `description` | string | 提示描述 | 空字符串 |
| `arguments` | array | 提示参数 | 自动生成 |
| `server` | string | 服务器名称 | 'default' |
| `enabled` | bool | 是否启用 | true |

### #[Resource]

| 参数 | 类型 | 描述 | 默认值 |
|------|------|------|--------|
| `name` | string | 资源名称 | 方法名 |
| `uri` | string | 资源 URI | 自动生成 |
| `description` | string | 资源描述 | 空字符串 |
| `mimeType` | string\|null | MIME 类型 | null |
| `size` | int\|null | 资源大小 | null |
| `server` | string | 服务器名称 | 'default' |
| `enabled` | bool | 是否启用 | true |
| `isTemplate` | bool | 是否为模板 | false |
| `uriTemplate` | array | URI 模板参数 | 空数组 |

## API 文档

### McpServerManager

MCP 服务器管理器，支持多服务器架构。

#### 方法

- `handle(string $server = 'default', ?RequestInterface $request = null): ResponseInterface` - 处理指定服务器的 MCP 请求
- `get(string $server = 'default'): McpServer` - 获取指定的 MCP 服务器实例
- `createMcpServer(string $name = 'McpServer', string $version = '1.0.0'): McpServer` - 创建新的 MCP 服务器实例

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
- `getSessionDetails(string $sessionId): ?array` - 获取会话详细信息（包含创建时间、最后活动时间、TTL）
- `getSessionCount(): int` - 获取活动会话总数

### 项目结构

```text
src/
├── ConfigProvider.php          # Hyperf 配置提供者
├── McpServerManager.php        # MCP 服务器管理器
├── RedisSessionManager.php     # Redis 会话管理器
├── Server.php                  # MCP 服务器类（向后兼容）
├── Annotation/                 # 注解定义
│   ├── McpAnnotation.php       # 基础注解类
│   ├── Prompt.php              # 提示注解
│   ├── Resource.php            # 资源注解
│   └── Tool.php                # 工具注解
└── Collector/                  # 注解收集器
    └── McpCollector.php        # MCP 注解收集器
```

### 核心组件

#### ConfigProvider

自动配置 Hyperf 依赖注入容器，注册默认的会话管理器、认证器和序列化器。

#### McpServerManager

MCP 服务器管理器，支持多服务器架构，提供：

- 多服务器实例管理
- 基于服务器名称的路由
- 工具、提示和资源的自动注册
- 统一的请求处理接口

#### RedisSessionManager

基于 Redis 的会话管理实现，提供：

- 会话创建和验证
- 自动过期管理
- 元数据存储
- 活动会话跟踪

#### 注解系统

- `#[Tool]`: 定义可调用的工具方法
- `#[Prompt]`: 定义智能提示模板
- `#[Resource]`: 定义可访问的资源

#### McpCollector

自动收集和注册所有带有 MCP 注解的方法，支持服务器分组管理和动态启用/禁用。

## 性能和最佳实践

### 会话管理优化

- **会话 TTL 设置**: 根据实际业务需求设置合适的会话过期时间，避免过长或过短
- **Redis 连接池**: 使用连接池来优化 Redis 连接性能
- **批量操作**: 对于大量会话操作，考虑使用 Redis 管道或事务

### 注解使用建议

- **合理分组**: 使用 `server` 参数对相关功能进行分组，便于管理和调试
- **描述信息**: 为每个工具、提示和资源提供清晰的描述信息
- **类型提示**: 充分利用 PHP 类型提示，框架会自动生成输入 Schema
- **禁用功能**: 使用 `enabled: false` 临时禁用某些功能，而不是删除代码

### 多服务器架构

Hyperf MCP Server 支持多服务器架构，允许你将不同类型的功能分组到不同的服务器中：

```php
<?php
// 数学计算服务器
#[Tool(name: 'add', server: 'math')]
public function add(int $a, int $b): int { return $a + $b; }

// 文本处理服务器
#[Tool(name: 'uppercase', server: 'text')]
public function uppercase(string $text): string { return strtoupper($text); }

// 默认服务器
#[Tool(name: 'general_tool')]
public function generalTool(): string { return 'Hello'; }
```

不同的路由可以处理不同的服务器：

```php
<?php
// 处理数学相关的请求
#[RequestMapping(path: '/mcp/math')]
public function handleMath() {
    return $this->mcpServerManager->handle('math');
}

// 处理文本相关的请求
#[RequestMapping(path: '/mcp/text')]
public function handleText() {
    return $this->mcpServerManager->handle('text');
}
```

### 错误处理

```php
<?php
use Dtyq\PhpMcp\Shared\Exceptions\ToolError;

#[Tool(name: 'safe_divide', description: 'Safely divide two numbers')]
public function safeDivide(float $a, float $b): float
{
    if ($b === 0.0) {
        throw new ToolError('Division by zero is not allowed');
    }
    return $a / $b;
}
```

## 向后兼容性

项目保留了 `Server` 类以提供向后兼容性。如果你使用的是旧版本的代码，可以继续使用：

```php
<?php
use Hyperf\McpServer\Server;

#[Controller]
class McpController
{
    public function __construct(private Server $server) {}
    
    #[RequestMapping(path: '/mcp')]
    public function handle()
    {
        return $this->server->handler(); // 使用默认服务器
    }
}
```

但建议升级到新的 `McpServerManager` 以享受多服务器架构的优势。

## 开发和测试

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
- [dtyq/php-mcp](https://github.com/dtyq/php-mcp) - 核心 MCP 实现库
- [Redis](https://redis.io)

## 更新日志

### v1.0.0 (开发中)

- 初始版本发布
- 支持基于注解的工具、提示和资源定义（`#[Tool]`、`#[Prompt]`、`#[Resource]`）
- Redis 会话管理，支持 UUID v4 格式的会话 ID
- 多服务器架构支持，允许功能分组到不同的服务器实例
- 完整的类型安全支持
- 会话监控和管理功能
- 支持会话元数据存储
- McpServerManager 统一管理多个 MCP 服务器实例
