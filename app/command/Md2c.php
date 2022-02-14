<?php

namespace app\command;

use think\console\Command;
use think\console\Input;
use think\console\Output;
use app\model\Table;
use app\model\Col;

class Md2c extends Command
{
    // 模型文件路径
    public const MODEL_PATH = './model.chnr.json';
    // 模型
    protected $models = [];
    // 字典映射
    protected $dict_map = [];

    // 忽略实体
    public const IGNORE_ENTITY = ['dict', 'dict_item', 'admin', 'config', 'menu', 'table', 'col'];

    protected function configure()
    {
        // 指令配置
        $this->setName('md2c')
            ->setDescription('Model design to coding');
    }

    protected function execute(Input $input, Output $output)
    {
        if (!file_exists(self::MODEL_PATH)) {
            $output->writeln('<error>' . self::MODEL_PATH . ' is not exist</error>');
        } else {
            $this->models = json_decode(file_get_contents(self::MODEL_PATH), true);
            $this->dict_map = array_column($this->models['dicts'], 'id');

            array_walk($this->models['entities'], [$this, 'entity2table']);

            $output->writeln('<info>Model design to coding Succeed</info>');
        }
    }

    // 实体转表格
    protected function entity2table($entity)
    {
        $code = strtolower($entity['defKey']);
        $table = Table::find($code);
        if ($table || in_array($code, self::IGNORE_ENTITY)) {
            return;
        }

        Table::create([
            'code'  => $code,
            'name'  => $entity['defName'],
            'props' => ['rowKey' => array_search(true, array_column($entity['fields'], 'primaryKey', 'defKey'))],
            'options' => [
                'columns' => [
                    ['type' => 'view', 'key' => 'view', 'title' => '查看'],
                    ['type' => 'edit', 'key' => 'edit', 'title' => '编辑'],
                    ['type' => 'delete', 'key' => 'delete', 'title' => '删除', 'confirm' => true],
                ],
                'toolbar' => [
                    ['type' => 'add', 'key' => 'add', 'title' => '新建'],
                    ['type' => 'export', 'key' => 'export', 'title' => '导出'],
                ],
                'batch' => [
                    ['type' => 'bdelete', 'key' => 'bdelete', 'title' => '批量删除'],
                ]
            ],
        ]);
        array_walk($entity['fields'], [$this, 'field2col'], $entity);

        $this->output->writeln('<info>' . $entity['defKey'] . ' ok.</info>');
    }

    // 属性转列
    protected function field2col($field, $index, $entity)
    {
        if (in_array($field['defKey'], ['update_time', 'delete_time', 'revision'])) {
            return;
        }

        $data = [
            'table_code'    => strtolower($entity['defKey']),
            'data_index'    => strtolower($field['defKey']),
            'title'         => $field['defName'],
            'tip'           => $field['comment'],
            'sort'          => $index
        ];
        if (!empty($field['refDict'])) {
            $dict = map_array_value($this->dict_map, $this->models['dicts'], $field['refDict']);
            $data['value_enum_dict_key'] = $dict ? $dict['defKey'] : '';
        }

        Col::create($data);
    }
}
