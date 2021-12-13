<?php

namespace app\command;

use think\console\Command;
use think\console\Input;
use think\console\Output;
use app\model\Admin;

class Init extends Command
{
    protected function configure()
    {
        // 指令配置
        $this->setName('init')
            ->setDescription('Init system command');
    }

    protected function execute(Input $input, Output $output)
    {
        $admin = Admin::findOrEmpty(1);
        if ($admin->isEmpty()) {
            $admin->id = 1;
            $admin->nickname = 'admin';
            $admin->username = 'admin';
            $admin->password = '123456';
            $admin->save();
            $output->writeln('<info>Admin(id=1) Created!</info>');
        } else {
            $output->writeln('<warning>Admin(id=1) Already Exists!</warning>');
        }

        $output->writeln('<info>Init System Succeed!</info>');
    }
}
