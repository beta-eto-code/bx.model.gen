<?php


namespace Bx\Model\Gen\Commands;


use Bx\Model\Gen\BitrixContext;
use Bx\Model\Gen\IblockGenerator;
use Bx\Model\Gen\SectionGenerator;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class GenerateSection extends Command
{
    protected function configure()
    {
        $this->setName('gen:section')
            ->setAliases(['gs'])
            ->setDescription('Генерация кода для раздела инфоблока')
            ->addArgument('type', InputArgument::REQUIRED, 'Тип инфоблока')
            ->addArgument('code', InputArgument::REQUIRED, 'Код инфоблока')
            ->addArgument('module', InputArgument::REQUIRED, 'Модуль для генерации кода')
            ->addOption('category', 'c', InputOption::VALUE_OPTIONAL);
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $type = $input->getArgument('type');
        $code = $input->getArgument('code');
        $module = $input->getArgument('module');
        $category = $input->getOption('category');

        $bitrixContext = new BitrixContext();
        $sectionGenerator = new SectionGenerator($type, $code, $module, $bitrixContext);
        if (!empty($category)) {
            $sectionGenerator->setCategory($category);    
        }
        
        $sectionGenerator->run();

        $output->writeln('Операция выполнена успешно.');
        return 1;
    }
}