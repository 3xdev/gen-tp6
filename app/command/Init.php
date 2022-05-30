<?php

namespace app\command;

use think\console\Command;
use think\console\Input;
use think\console\Output;
use think\facade\Db;
use think\facade\View;
use app\model\SystemDict;
use app\model\SystemAdmin;
use app\model\SystemMenu;

class Init extends Command
{
    // 模型文件路径
    public const MODEL_PATH = './model.pdma.json';
    // 模型
    protected $models = [];
    // 实体映射
    protected $entity_map = [];
    // 字典映射
    protected $dict_map = [];
    // 数据域映射
    protected $domain_map = [];
    // 数据类型映射
    protected $datatype_map = [];
    // 数据库映射键
    protected $database_key = [];

    protected function configure()
    {
        // 指令配置
        $this->setName('init')
            ->setDescription('Init system command');
    }

    protected function execute(Input $input, Output $output)
    {
        // 初始化数据库
        if (!file_exists(self::MODEL_PATH)) {
            $output->writeln('<error>' . self::MODEL_PATH . ' is not exist</error>');
        } else {
            $this->models = json_decode(file_get_contents(self::MODEL_PATH), true);
            $this->database_key = array_column($this->models['profile']['dataTypeSupports'], 'id', 'defKey')[strtoupper(config('database.default'))];
            $this->entity_map = array_column($this->models['entities'], 'id');
            $this->dict_map = array_column($this->models['dicts'], 'id');
            $this->domain_map = array_column($this->models['domains'], 'id');
            $this->datatype_map = array_column($this->models['dataTypeMapping']['mappings'], 'id');

            View::assign('prefix', config('database.connections.' . config('database.default') . '.prefix'));
            array_walk($this->models['entities'], [$this, 'createDDL']);
            array_walk($this->models['dicts'], [$this, 'insertDict']);

            $output->writeln('<info>Database Init Succeed</info>');
        }

        // 初始化管理员
        $admin = SystemAdmin::findOrEmpty(1);
        if ($admin->isEmpty()) {
            $admin->id = 1;
            $admin->nickname = 'admin';
            $admin->username = 'admin';
            $admin->password = '123456';
            $admin->save();
            $output->writeln('<info>SystemAdmin(id=1) Created!</info>');
        } else {
            //$output->writeln('<warning>SystemAdmin(id=1) Already Exists!</warning>');
        }

        // 初始化管理菜单
        $menu = SystemMenu::where('path', '/system/menu')->findOrEmpty();
        if ($menu->isEmpty()) {
            $menu->saveAll([
                ['parent_id' => '0', 'name' => '系统管理', 'path' => '', 'icon' => 'setting'],
                ['parent_id' => '1', 'name' => '系统配置', 'path' => '/system/setting'],
                ['parent_id' => '1', 'name' => '字典管理', 'path' => '/system/dict'],
                ['parent_id' => '1', 'name' => '配置项管理', 'path' => '/system/config'],
                ['parent_id' => '1', 'name' => '管理员管理', 'path' => '/system/admin'],
                ['parent_id' => '1', 'name' => '菜单管理', 'path' => '/system/menu'],
                ['parent_id' => '1', 'name' => '表格管理', 'path' => '/system/table'],
            ]);
            $output->writeln('<info>SystemMenu Created!</info>');
        } else {
            //$output->writeln('<warning>SystemMenu Already Exists!</warning>');
        }

        $output->writeln('<info>Init System Succeed!</info>');
    }

    // 创建表
    protected function createDDL($entity)
    {
        $entity['pks'] = implode(',', array_column(array_filter($entity['fields'], fn($field) => $field['primaryKey']), 'defKey'));
        array_walk($entity['fields'], [$this, 'walkField']);

        View::assign('entity', $entity);
        View::assign('entity', $entity);
        try {
            Db::execute(View::fetch('database/' . config('database.default') . '/ddl_create_table'));
        } catch (\Exception $e) {
            //$this->output->writeln('<warning>' . $e->getMessage() . '</warning>');
        }

        foreach ($entity['indexes'] as $index) {
            $fieldMap = array_column($entity['fields'], 'id');
            $indexFields = [];
            foreach ($index['fields'] as $field) {
                $indexFields[] = map_array_value($fieldMap, $entity['fields'], $field['fieldDefKey']);
            }
            $index['fks'] = implode(',', array_column($indexFields, 'defKey'));
            View::assign('index', $index);
            try {
                Db::execute(View::fetch('database/' . config('database.default') . '/ddl_create_index'));
            } catch (\Exception $e) {
                //$this->output->writeln('<warning>' . $e->getMessage() . '</warning>');
            }
        }

        $this->output->writeln('<info>table ' . $entity['defKey'] . ' ok.</info>');
    }
    protected function walkField(&$field)
    {
        $domain = map_array_value($this->domain_map, $this->models['domains'], $field['domain']);
        $datatype = $domain ? map_array_value($this->datatype_map, $this->models['dataTypeMapping']['mappings'], $domain['applyFor']) : null;
        empty($field['type']) && $field['type'] = $datatype ? $datatype[$this->database_key] : '';
        empty($field['len']) && $field['len'] = $domain ? $domain['len'] : 0;
        empty($field['scale']) && $field['scale'] = $domain ? $domain['scale'] : 0;
    }

    // 插入字典
    protected function insertDict($dict)
    {
        $model = SystemDict::find($dict['defKey']);
        if ($model) {
            return;
        }

        $model = SystemDict::create([
            'key_' => $dict['defKey'],
            'label' => $dict['defName'],
            'intro' => $dict['intro'],
        ]);
        empty($dict['items']) || $model->items()->saveAll(array_map(fn($item) => [
            'key_' => $item['defKey'],
            'label' => $item['defName'],
            'sort_' => $item['sort'],
            'intro' => $item['intro'],
        ], $dict['items']));

        $this->output->writeln('<info>dict ' . $dict['defKey'] . ' ok.</info>');
        return;
    }
}
