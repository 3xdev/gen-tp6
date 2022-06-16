Gen(艮快低代码开发)
==================

> 运行环境要求PHP7.4+，兼容PHP8.0。
> 快速低代码开发
> 领域驱动设计
> 模型设计自动转化源码

## 依赖包

* topthink/framework 6.0
* topthink/think-orm 2.0
* thans/tp-jwt-auth 1.1
* casbin/think-authz 1.5
* godruoyi/php-snowflake 1.1
* jaguarjack/think-filesystem-cloud 1.0

## 开发依赖包

* squizlabs/php_codesniffer 3.6
* captainhook/captainhook 5.10
* ramsey/conventional-commits 1.1

## Gen管理端

* Ant Design Pro V5
https://github.com/3xdev/gen-adp5

## 安装依赖

```bash
composer install
```

## 配置环境变量

```bash
cp .example.env .env
vi .env
```

## 运行项目初始化指令

* 初始化数据库(从PDManer模型)
* 初始化管理员，用户名admin，密码123456
* 初始化管理菜单

```bash
php think init
```

## 运行项目

```bash
# 指定800端口运行
php think run -p 800
```

## 模型设计自动生成源码

* 根椐模型数据表生成数据库表(从PDManer模型)
* 根据模型数据表产生系统表格及列配置数据
* 根据模型数据表及关系图生成控制器、验证器、模型源码

```bash
php think md2c
```


## 约定路由

* 规则

| 规范 | 请求 | 路由 | 控制器/操作 |
| ---- | ---- | ---- | ---- |
| CRUD | GET  | /api/admin/crud/:controller | admin.:controller/index |
| CRUD | POST | /api/admin/crud/:controller | admin.:controller/create |
| CRUD | GET  | /api/admin/crud/:controller/:id | admin.:controller/read |
| CRUD | PUT  | /api/admin/crud/:controller/:id | admin.:controller/update |
| CRUD | DELETE | /api/admin/crud/:controller/:ids | admin.:controller/delete |
| REST | GET  | /api/admin/rest/:controller/:action/:ids | admin.:controller/get:action |
| REST | POST  | /api/admin/rest/:controller/:action/:ids | admin.:controller/post:action |
| REST | PUT  | /api/admin/rest/:controller/:action/:ids | admin.:controller/put:action |
| REST | DELETE | /api/admin/rest/:controller/:action/:ids | admin.:controller/delete:action |

* 示例

| 规范 | 请求 | 路由 | 控制器/操作 |
| ---- | ---- | ---- | ---- |
| CRUD | GET  | /api/admin/crud/admin_log | admin.AdminLog/index |
| CRUD | POST | /api/admin/crud/admin_log | admin.AdminLog/create |
| CRUD | GET  | /api/admin/crud/admin_log/1 | admin.AdminLog/read |
| CRUD | PUT  | /api/admin/crud/admin_log/1 | admin.AdminLog/update |
| CRUD | DELETE | /api/admin/crud/admin_log/1,2 | admin.AdminLog/delete |
| REST | GET  | /api/admin/rest/admin_log/act/1 | admin.AdminLog/getAct |
| REST | POST  | /api/admin/rest/admin_log/act/1 | admin.AdminLog/postAct |
| REST | PUT  | /api/admin/rest/admin_log/act/1 | admin.AdminLog/putAct |
| REST | PUT  | /api/admin/rest/admin_log/act/1,2 | admin.AdminLog/putAct |
| REST | DELETE | /api/admin/rest/admin_log/act/1,2 | admin.AdminLog/deleteAct |

## 源码规范检查

```bash
composer lint
```

## 源码规范修复

```bash
composer lint-fix
```

## 开发常用方法

### 公共函数及方法
```php
// 获取系统配置
system_config('site_name');
\app\model\SystemConfig::fetchCache('site_name');

// 获取系统字典
system_dict('config_tab');
\app\model\SystemDict::fetchCache('config_tab');
```

### 模型搜索器-区间搜索
示例1：
1、表格设计新增列名 create_time[]
2、Base模型已有searchCreateTimeAttr

示例2：
1、表格设计新增列名 price[]
2、模型添加搜索器搜索价格区间
```php
// 价格区间搜索器
public function searchPriceAttr($query, $value, $data)
{
    empty($value) || $query->whereBetween('price', $value[0], $value[1]);
}
```

### 模型搜索器-关联搜索
示例：
1、表格设计新增列名 user.nickname , user.mobile
2、模型添加搜索器搜索关联用户信息
```php
// 关联用户搜索器
public function searchUserAttr($query, $value, $data)
{
    $map = array_filter(json_decode($value , true));
    empty($map) || $query->whereIn('user_id', function ($sq) use ($map) {
        $sq = (new User())->db();
        $sq->field($sq->getPk());
        foreach ($map as $k => $v) {
            $sq->whereLike($k, "%{$v}%");
        }
    });
}
```




## Git提交规范

* commit-msg 必须遵循 约定式提交
* 提交的分支名 必须是 feature/{开发人员姓名首字母缩写}-{YYMMDD}
* 提交的源码 必须符合 PSR12

[约定式提交]: https://www.conventionalcommits.org/zh-hans/v1.0.0/
[PSR-12]: https://www.php-fig.org/psr/psr-12/

## PDManer设计数据模型

* model.pdma.json

## apidoc生成接口文档

```bash
apidoc -i app/controller/open -o public/doc/open
apidoc -i app/controller/admin -o public/doc/admin
```
