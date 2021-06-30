标准化项目实践
===============

> 运行环境要求PHP7.4+，兼容PHP8.0。

## 依赖包

* topthink/framework 6.0.8
* topthink/think-orm 2.0.40

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
php think run
```

## apidoc生成接口文档

```bash
apidoc -i app/controller/open/ -o open-apis/
apidoc -i app/controller/admin/ -o admin-apis/
```
