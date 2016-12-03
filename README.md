# swoole-timers
swoole 异步定时任务器

##环境依赖
> * Swoole 1.8.x+
> * PHP 5.4+
> * Composer
> * Redis

##依赖
> * swoole-rpc         <https://github.com/longxinH/swoole-rpc>

----------

#快速开始
```shell
 composer install
```
> 由于composer需要翻墙下载packagist镜像，墙内的同学可以使用如下方法
```shell
 git  clone  https://github.com/longxinH/swoole-rpc.git
 cd swoole-rpc/
 composer install
```
> 引入 vendor/autoload.php即可

##运行redis
```shell
redis-server
```

##运行服务指令
```
 start | stop | reload | restart | help
```

##运行定时服务
```
 cd swoole-timers/service/server/
 php swoole.php start
```

##访问管理地址
```
localhost/admin/
```
----------

#感谢
> * 可自行更换其他MVC框架
后台UI  https://github.com/qianqiulin/ace
MVC  https://github.com/leo108/SinglePHP 
