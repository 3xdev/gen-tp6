标准化项目实践
===============

> 运行环境要求PHP7.4+，兼容PHP8.0。

## 依赖包

* topthink/framework 6.0
* topthink/think-orm 2.0
* thans/tp-jwt-auth 1.1

## 开发依赖包

* squizlabs/php_codesniffer 3.6
* captainhook/captainhook 5.10
* ramsey/conventional-commits 1.1

## 安装依赖

```bash
composer install
```

## 配置环境变量

```bash
cp .example.env .env
vi .env
```

## 运行项目

```bash
# 指定800端口运行
php think run -p 800
```

## 源码规范检查

```bash
composer lint
```

## 源码规范修复

```bash
composer lint-fix
```

## 开发常用方法

```php
// 获取系统配置
system_config('site_name');
\app\model\Config::fetchCache('site_name');

// 获取系统字典
system_dict('config_tab');
\app\model\Dict::fetchCache('config_tab');
```

## Git提交规范

* commit-msg 必须遵循 约定式提交
* 提交的分支名 必须是 feature/{开发人员姓名首字母缩写}-{YYMMDD}
* 提交的源码 必须符合 PSR12

[约定式提交]: https://www.conventionalcommits.org/zh-hans/v1.0.0/
[PSR-12]: https://www.php-fig.org/psr/psr-12/

## CHINER设计数据模型

* model.chnr.json

## apidoc生成接口文档

```bash
apidoc -c app/controller/open/apidoc.json  -i app/controller/open/  -o public/doc/open/
apidoc -c app/controller/admin/apidoc.json -i app/controller/admin/ -o public/doc/admin/
```
