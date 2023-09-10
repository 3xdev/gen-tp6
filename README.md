# Gen(艮快低代码开发)

## 特性

> 运行环境要求 PHP8.0+
> 快速低代码开发
> 领域驱动设计
> 模型设计自动转化源码

## 依赖包

- topthink/framework 6.1
- topthink/think-orm 3.0
- topthink/think-view 2.0
- casbin/think-authz 1.5
- yzh52521/tp-jwt-auth 2.0
- yzh52521/think-filesystem 3.0
- godruoyi/php-snowflake 2.2
- openspout/openspout 4.13
- bluem/tree 3.2
- nette/php-generator 4.0
- nette/utils 4.0

## 开发依赖包

- squizlabs/php_codesniffer 3.6
- captainhook/captainhook 5.10
- ramsey/conventional-commits 1.1

## Gen 管理端

- Ant Design Pro V5
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

- 初始化数据库(从 PDManer 模型)
- 初始化管理员，用户名 admin，密码 123456
- 初始化管理菜单

```bash
php think init
```

## 运行项目

```bash
# 指定800端口运行
php think run -p 800
```

## 模型设计自动生成源码

- 根椐模型数据表生成数据库表(从 PDManer 模型)
- 根据模型数据表产生系统表格及列配置数据
- 根据模型数据表及关系图生成控制器、验证器、模型源码

```bash
php think md2c
```

## 约定路由

- 规则

| 规范 | 请求    | 路由                                      | 控制器/操作                      |
| ---- | ------ | ---------------------------------------- | ------------------------------- |
| CRUD | GET    | /api/admin/crud/:controller              | admin.:controller/index         |
| CRUD | POST   | /api/admin/crud/:controller              | admin.:controller/create        |
| CRUD | GET    | /api/admin/crud/:controller/:id          | admin.:controller/read          |
| CRUD | PUT    | /api/admin/crud/:controller/:id          | admin.:controller/update        |
| CRUD | DELETE | /api/admin/crud/:controller/:ids         | admin.:controller/delete        |
| REST | GET    | /api/admin/rest/:controller/:action/:ids | admin.:controller/get:action    |
| REST | POST   | /api/admin/rest/:controller/:action/:ids | admin.:controller/post:action   |
| REST | PUT    | /api/admin/rest/:controller/:action/:ids | admin.:controller/put:action    |
| REST | DELETE | /api/admin/rest/:controller/:action/:ids | admin.:controller/delete:action |

- 示例

| 规范 | 请求    | 路由                               | 控制器/操作               |
| ---- | ------ | --------------------------------- | ------------------------ |
| CRUD | GET    | /api/admin/crud/admin_log         | admin.AdminLog/index     |
| CRUD | POST   | /api/admin/crud/admin_log         | admin.AdminLog/create    |
| CRUD | GET    | /api/admin/crud/admin_log/1       | admin.AdminLog/read      |
| CRUD | PUT    | /api/admin/crud/admin_log/1       | admin.AdminLog/update    |
| CRUD | DELETE | /api/admin/crud/admin_log/1,2     | admin.AdminLog/delete    |
| REST | GET    | /api/admin/rest/admin_log/act/1   | admin.AdminLog/getAct    |
| REST | POST   | /api/admin/rest/admin_log/act/1   | admin.AdminLog/postAct   |
| REST | PUT    | /api/admin/rest/admin_log/act/1   | admin.AdminLog/putAct    |
| REST | PUT    | /api/admin/rest/admin_log/act/1,2 | admin.AdminLog/putAct    |
| REST | DELETE | /api/admin/rest/admin_log/act/1,2 | admin.AdminLog/deleteAct |

## 源码规范检查

```bash
composer lint
```

## 源码规范修复

```bash
composer lint-fix
```

## 开发流程

1、设计 pdmaner 模型，索引，关联
2、php think run md2c 生成表格、代码
3、后端管理表格设计（列、操作）
4、后端管理表格设计操作 引用表单 (有其他表单业务)
5、后单管理 添加表单 (有其他表单业务)
6、具体控制器和模型编码

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


### 数据库查询

```php
// 相同条件的OR查询
User::where('username|mobile', 'xxxxx')->select();
User::where('username|mobile', 'like', 'test%')->select();

// 不同条件的OR查询
User::where('status', 1)->where(function ($query) use($username, $mobile) {
    $query->whereOr([['username', '=', $username], ['mobile', '=', $mobile]]);
})->select();

// 不同组条件的OR查询
User::where('status', 1)->where(function ($query) use($username, $mobile) {
    $query->whereOr([
        [
            ['username', '=', $username],
        ],
        [
            ['mobile', '<>', ''],
            ['mobile', '=', $mobile],
        ],
    ]);
})->select();
// SELECT * FROM `gen_user` WHERE (  `status` = 1  AND (  ( `username` = 'liux' )  OR ( `mobile` <> '' AND `mobile` = '13012345678' ) ) ) AND `gen_user`.`delete_time` = '0'
```

### 模型获取器及修改器

示例 1：
1、表格设计新增列名 user_count
2、模型添加关联用户和用户数获取器

```php
// 关联用户总数
public function getUserCountAttr($value, $data)
{
    return $this->users()->count();
}

// 关联用户
public function users()
{
    return $this->hasMany(User::class, 'group_id');
}
```

示例 2：

```php
// ,分隔转数组
public function getUserIdsAttr($value, $data)
{
    return $value ? array_map(fn($value) => intval($value), explode(',', $value)) : [];
}
// 数组存,分隔字符串
public function setUserIdsAttr($value, $data)
{
    return is_array($value) ? implode(',', $value) : $value;
}
```

示例 3：

```php
// 时间格式化
protected function getStarttimeAttr($value)
{
    return $value ? date('Y-m-d H:i:s', $value) : '';
}
protected function setStarttimeAttr($value)
{
    return is_numeric($value) ? $value : strtotime($value);
}
```

### 模型搜索器-模糊搜索

示例 1：
1、模型添加搜索器模糊搜索

```php
// 城市名模糊搜索
public function searchCitynameAttr($query, $value, $data)
{
    if (!empty($value)) {
        $query->whereLike('cityname', '%' . $value . '%');
    }
}
```

示例 2：
1、表格设计新增列名 bind_info
2、模型添加搜索器模糊搜索

```php
// 绑定信息模糊搜索
public function searchBindInfoAttr($query, $value, $data)
{
    if (!empty($value)) {
        $query->where('bind_name|bind_mobile', 'like', '%' . $value . '%');
    }
}
```

### 模型搜索器-区间搜索

示例 1：
1、表格设计新增列名 create_time[]
2、Base 模型已有 searchCreateTimeAttr

示例 2：
1、表格设计新增列名 price[]
2、模型添加搜索器搜索价格区间

```php
// 价格区间搜索器
public function searchPriceAttr($query, $value, $data)
{
    empty($value) || $query->whereBetween('price', $value[0], $value[1]);
}
```

### 模型搜索器-直接关联搜索

示例：
1、表格设计新增列名 user.nickname , user.mobile
2、模型添加搜索器搜索关联用户信息(大表查小表用 in 子查询，小表查大表用 exists 子查询)

```php
// 关联用户搜索器
public function searchUserAttr($query, $value, $data)
{
    $map = $this->parseSearch('user', json_decode($value, true));
    empty($map) || $query->whereIn('user_id', function (&$sq) use ($map) {
        $sq = (new User())->db()->alias('user');
        $sq->field('id')->where($map);
    });
}

// 关联用户搜索器
public function searchUserAttr($query, $value, $data)
{
    $map = $this->parseSearch('user', json_decode($value, true));
    empty($map) || $query->whereExists(function (&$sq) use ($map, $query) {
        $sq = (new User())->db()->alias('user');
        $sq->whereExp('id', '=' . $query->getTable() . '.user_id')->where($map);
    });
}
```

### 模型搜索器-间接/多表关联搜索

示例：
1、表格设计新增列名 order.no , order.sellerUser.mobile
2、模型添加搜索器搜索关联商品信息(大表查小表用 in 子查询，小表查大表用 exists 子查询)

```php
// 关联定单搜索器(自动别名)
public function searchOrderAttr($query, $value, $data)
{
    $value = json_decode($value, true);
    $map = $this->parseSearch('order', $value);
    empty($map) || $query->whereExists(function (&$sq) use ($value, $map) {
        $sq = (new Order())->db()->alias('order');
        empty($value['sellerUser']) || $sq->join('user sellerUser', 'order.seller_user_id=sellerUser.id');
        $sq->whereExp('order.id', '=' . $this->db()->getTable() . '.order_id')->where($map);
    });
}

// 关联定单搜索器(定义别名)
public function searchOrderAttr($query, $value, $data)
{
    $value = json_decode($value, true);
    $map = $this->parseSearch('order', $value, ['order' => 'o', 'order.sellerUser' => 'u']);
    empty($map) || $query->whereExists(function (&$sq) use ($value, $map) {
        $sq = (new Order())->db()->alias('o');
        empty($value['sellerUser']) || $sq->join('user u', 'o.seller_user_id=u.id');
        $sq->whereExp('o.id', '=' . $this->db()->getTable() . '.order_id')->where($map);
    });
}
```

### 验证-表记录唯一检查

示例：
1、验证 添加 验证规则

```php
// 情景1：必选，单一字段验证唯一表记录
protected $rule = [
    'goods_id|商品' => 'require|checkModelUnique:\\app\\model\\H5Goods',
];

// 情景2：条件必选，多个字段验证唯一表记录
protected $rule = [
    'goods_id|商品' => 'requireIf:type,goods|checkModelUnique:\\app\\model\\H5Carousel,goods_id^company_id',
];
```

### 表格设计-列下拉框多选

示例：
1、表格列类型设为下拉框，修改 编辑扩展 中的 组件属性 为 {"mode":"multiple"}
2、模型设置表格列为 json 类型字段（当字段值为数组形式的 json 数据时）

```php
protected $json = ['user_ids'];
protected $jsonAssoc = true;
```

3、模型设置表格列获取器和修改器（当字段值为半角逗号分隔的字符串时）

```php
public function getUserIdsAttr($value, $data)
{
    return json_decode($value) ?: [];
}
public function setUserIdsAttr($value, $data)
{
    return is_array($value) ? json_encode($value) : '[]';
}
```

## Git 提交规范

- commit-msg 必须遵循 约定式提交
- 提交的分支名 必须是 feature/{开发人员姓名首字母缩写}-{YYMMDD}
- 提交的源码 必须符合 PSR12

[约定式提交]: https://www.conventionalcommits.org/zh-hans/v1.0.0/
[psr-12]: https://www.php-fig.org/psr/psr-12/

## PDManer 设计数据模型

- model.pdma.json

## apidoc 生成接口文档

```bash
apidoc -i app/controller/open -o public/doc/open
apidoc -i app/controller/admin -o public/doc/admin
```
