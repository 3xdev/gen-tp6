<?php

namespace app\command;

use think\console\Command;
use think\console\Input;
use think\console\Output;
use think\helper\Str;
use app\model\SystemTable;
use app\model\SystemCol;
use Nette\PhpGenerator\PhpFile;
use Nette\PhpGenerator\PsrPrinter;

class Md2c extends Command
{
    // 模型文件路径
    public const MODEL_PATH = './model.pdma.json';
    // 模型
    protected $models = [];
    // 实体映射
    protected $entity_map = [];
    // 字典映射
    protected $dict_map = [];
    // UI建议映射
    protected $uihint_map = [];

    // 忽略前缀
    public const IGNORE_PREFIX = 'system';

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
            $this->uihint_map = array_column($this->models['profile']['uiHint'], 'id');

            array_walk($this->models['entities'], [$this, 'entity2class']);
            array_walk($this->models['entities'], [$this, 'entity2table']);

            $output->writeln('<info>Model design to coding Succeed</info>');
        }
    }

    // 实体生成类
    protected function entity2class($entity)
    {
        if (stripos(strtolower($entity['defKey']), self::IGNORE_PREFIX) === 0) {
            return;
        }

        // 生成模型
        $file = new PhpFile();
        $namespace = $file->addNamespace('app\model');
        $class = $namespace->addClass(name_class($entity['defKey']));
        $class->addComment($entity['defName'] . '模型');
        $class->setExtends(\app\model\Base::class);
        $fks = array_map('strtolower', array_column($entity['fields'], 'defKey'));
        $pks = array_map('strtolower', array_column(array_filter($entity['fields'], fn($field) => $field['primaryKey']), 'defKey'));
        // 主键
        if (count($pks) > 1 || (count($pks) == 1 && $pks[0] != 'id')) {
            $class->addProperty('pk', count($pks) > 1 ? $pks : $pks[0])->setProtected();
        }
        // 软删除
        if (in_array('delete_time', $fks)) {
            $namespace->addUse(\think\model\concern\SoftDelete::class);
            $class->addTrait(\think\model\concern\SoftDelete::class);
        }
        // 模型关联
        foreach ($this->models['diagrams'] as $diagram) {
            $mapCells = array_column($diagram['canvasData']['cells'], 'id');
            foreach ($diagram['canvasData']['cells'] as $cell) {
                if ($cell['shape'] == 'erdRelation') {
                    $source = map_array_value($mapCells, $diagram['canvasData']['cells'], $cell['source']['cell']);
                    $target = map_array_value($mapCells, $diagram['canvasData']['cells'], $cell['target']['cell']);
                    if ($source['originKey'] == $entity['id']) {
                        $relEntity = map_array_value($this->entity_map, $this->models['entities'], $target['originKey']);
                        $class->addMethod(name_relation($relEntity['defKey']) . ($cell['relation'] == '1:n' ? 's' : ''))
                            ->setBody('return $this->' . ($cell['relation'] == '1:n' ? 'hasMany' : 'hasOne') . '(' . name_class($relEntity['defKey']) . '::class);');
                    }
                    if ($target['originKey'] == $entity['id']) {
                        $relEntity = map_array_value($this->entity_map, $this->models['entities'], $source['originKey']);
                        $class->addMethod(name_relation($relEntity['defKey']))
                            ->setBody('return $this->belongsTo(' . name_class($relEntity['defKey']) . '::class);');
                    }
                }
            }
        }
        $path = $this->app->getBasePath() . 'model' . DIRECTORY_SEPARATOR . name_class($entity['defKey']) . '.php';
        if (file_exists($path)) {
            //$this->output->writeln('<warning>' . $path . ' Already Exists!</warning>');
        } else {
            file_put_contents($path, (new PsrPrinter())->printFile($file));
            $this->output->writeln('<info>' . $path . ' Created!</info>');
        }

        // 生成验证器
        $file = new PhpFile();
        $namespace = $file->addNamespace('app\validate');
        $namespace->addUse(\think\Validate::class);
        $class = $namespace->addClass(name_class($entity['defKey']));
        $class->addComment($entity['defName'] . '验证器');
        $class->setExtends(\think\Validate::class);
        $class->addProperty('rule', [])->setProtected()->addComment('验证规则');
        $path = $this->app->getBasePath() . 'validate' . DIRECTORY_SEPARATOR . name_class($entity['defKey']) . '.php';
        if (file_exists($path)) {
            //$this->output->writeln('<warning>' . $path . ' Already Exists!</warning>');
        } else {
            file_put_contents($path, (new PsrPrinter())->printFile($file));
            $this->output->writeln('<info>' . $path . ' Created!</info>');
        }

        // 生成管理控制器
        $file = new PhpFile();
        $namespace = $file->addNamespace('app\controller\admin');
        $class = $namespace->addClass(name_class($entity['defKey']));
        $class->addComment($entity['defName'] . '管理控制器');
        $class->setExtends(\app\controller\admin\Crud::class);
        $class->addMethod('initialize')->setProtected()->setBody('$this->model = new \app\model\\' . name_class($entity['defKey']) . '();');
        $path = $this->app->getBasePath() . 'controller' . DIRECTORY_SEPARATOR . 'admin' . DIRECTORY_SEPARATOR . name_class($entity['defKey']) . '.php';
        if (file_exists($path)) {
            //$this->output->writeln('<warning>' . $path . ' Already Exists!</warning>');
        } else {
            file_put_contents($path, (new PsrPrinter())->printFile($file));
            $this->output->writeln('<info>' . $path . ' Created!</info>');
        }
    }


    // 实体转表格
    protected function entity2table($entity)
    {
        $table = SystemTable::find(strtolower($entity['defKey']));
        if ($table || stripos(strtolower($entity['defKey']), self::IGNORE_PREFIX) === 0) {
            return;
        }

        SystemTable::create([
            'code'  => strtolower($entity['defKey']),
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
        if (in_array(strtolower($field['defKey']), ['delete_time', 'revision'])) {
            return;
        }

        $data = [
            'table_code'    => strtolower($entity['defKey']),
            'data_index'    => strtolower($field['defKey']),
            'value_type'    => 'text',
            'title'         => $field['defName'],
            'tip'           => $field['comment'],
            'default_value' => trim($field['defaultValue'], "'"),
            'hide_in_table' => $field['hideInGraph'] ? 1 : 0,
            'hide_in_form'  => ($field['hideInGraph'] || $field['primaryKey']) ? 1 : 0,
            'sort'          => $index
        ];
        if (!empty($field['refDict'])) {
            $dict = map_array_value($this->dict_map, $this->models['dicts'], $field['refDict']);
            if ($dict) {
                $data['value_enum_rel'] = ['dict', $dict['defKey']];
                $data['value_type'] = 'select';
                $data['filters'] = 1;
            }
        }
        if (!empty($field['uiHint'])) {
            $uihint = map_array_value($this->uihint_map, $this->models['profile']['uiHint'], $field['uiHint']);
            if ($uihint) {
                $data['value_type'] = $uihint['defKey'];
            }
        }

        SystemCol::create($data);
    }
}
