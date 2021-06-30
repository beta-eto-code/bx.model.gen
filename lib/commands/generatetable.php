<?php


namespace Bx\Model\Gen\Commands;


use Bx\Model\Gen\BitrixContext;
use Bx\Model\Gen\TableGenerator;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class GenerateTable extends Command
{
    protected function configure()
    {
        $this->setName('gen:table')
            ->setAliases(['gt'])
            ->setDescription('Генерация кода для таблицы')
            ->addArgument('table', InputArgument::REQUIRED, 'Название таблицы')
            ->addArgument('module', InputArgument::REQUIRED, 'Модуль для генерации кода')
            ->addOption('category', 'c', InputOption::VALUE_OPTIONAL)
            ->addOption('basename', 'b', InputOption::VALUE_OPTIONAL);
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $table = $input->getArgument('table');
        $module = $input->getArgument('module');
        $category = $input->getOption('category');
        $baseName = $input->getOption('basename');

        $bitrixContext = new BitrixContext();
        $tableGenerator = new TableGenerator($table, $module, $bitrixContext);
        if (!empty($category)) {
            $tableGenerator->setCategory($category);
        }
        if (!empty($baseName)) {
            $tableGenerator->setBaseName($baseName);
        }

        $tableGenerator->run();

        $output->writeln('Операция выполнена успешно.');
        return 1;
    }
}
