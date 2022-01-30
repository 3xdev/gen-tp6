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
    // 实体映射
    protected $entity_map = [];
    // 字典映射
    protected $dict_map = [];

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
            $this->entity_map = array_column($this->models['entities'], 'id');
            $this->dict_map = array_column($this->models['dicts'], 'id');

            $this->models && isset($this->models['entities']) && array_walk($this->models['entities'], [$this, 'entity2table']);

            $output->writeln('<info>Model design to coding Succeed</info>');
        }
    }

    // 实体转表格
    protected function entity2table($entity)
    {
        $table = Table::find(string_remove_prefix($entity['defKey'], env('database.prefix', '')));
        if ($table) {
            return;
        }

        Table::create([
            'code'  => string_remove_prefix($entity['defKey'], env('database.prefix', '')),
            'name'  => $entity['defName'],
        ]);
        isset($entity['fields']) && array_walk($entity['fields'], [$this, 'field2col'], $entity);

        $this->output->writeln('<info>' . $entity['defKey'] . ' ok.</info>');
    }

    // 属性转列
    protected function field2col($field, $key, $entity)
    {
        if (in_array($field['defKey'], ['update_time', 'delete_time', 'revision'])) {
            return;
        }

        $data = [
            'table_code'    => string_remove_prefix($entity['defKey'], env('database.prefix', '')),
            'data_index'    => $field['defKey'],
            'title'         => $field['defName'],
            'tip'           => $field['comment'],
            'sort'          => $key
        ];
        if (!empty($field['refDict'])) {
            $dict = map_array_value($this->dict_map, $this->models['dicts'], $field['refDict']);
            $data['value_enum_dict_key'] = $dict ? $dict['defKey'] : '';
        }

        Col::create($data);
    }
}
