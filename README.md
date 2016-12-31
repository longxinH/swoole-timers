# swoole-timers
swoole 异步定时任务器

##环境依赖
> * Swoole 1.8.x+
> * PHP 5.4+
> * Composer
> * Redis

##基础组件
> * swoole-rpc  <https://github.com/longxinH/swoole-rpc>

----------

#快速开始
```shell
 composer install
```
> 由于composer需要翻墙下载packagist镜像，墙内的同学可以使用如下方法
```shell
 git clone https://github.com/longxinH/swoole-rpc.git
 cd swoole-rpc/
 composer install
```
> swoole-timers/service/server/swoole.php文件中
```php
 include 'youpath/vendor/autoload.php';
```

> swoole-timers/admin/index.php文件中
```php
 include 'youpath/vendor/autoload.php';
```

##运行redis
```shell
redis-server
```

##运行服务指令
```shell
 start | stop | reload | restart | help
```

##运行定时服务
```shell
 cd swoole-timers/service/server/
 php swoole.php start
```

##访问管理地址
```
http://localhost/swoole-timers/admin/
```

##定时任务规则
1. 支持域名地址。
2. 暂时只支持PHP语法的脚本。忽略php开始```<?php```和结束标签 ```?>```
```php
file_put_contents('/tmp/t.log', date('Y-m-d H:i:s') . PHP_EOL, FILE_APPEND);
```

----------

#感谢

> * 后台UI  https://github.com/qianqiulin/ace

> * MVC (可自行更换其他MVC框架)  https://github.com/leo108/SinglePHP 
