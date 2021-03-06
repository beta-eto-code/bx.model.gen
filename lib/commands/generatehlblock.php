<?php


namespace Bx\Model\Gen\Commands;


use Bx\Model\Gen\BitrixContext;
use Bx\Model\Gen\HlBlockGenerator;
use Exception;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class GenerateHlBlock extends Command
{
    protected function configure()
    {
        $this->setName('gen:hlblock')
            ->setAliases(['gh'])
            ->setDescription('Генерация кода для HL блока')
            ->addArgument('code', InputArgument::REQUIRED, 'Код HL блока')
            ->addArgument('module', InputArgument::REQUIRED, 'Модуль для генерации кода')
            ->addOption('category', 'c', InputOption::VALUE_OPTIONAL);
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return void
     * @throws Exception
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $code = $input->getArgument('code');
        $module = $input->getArgument('module');
        $category = $input->getOption('category');

        $bitrixContext = new BitrixContext();
        $tableGenerator = new HlBlockGenerator($code, $module, $bitrixContext);
        if (!empty($category)) {
            $tableGenerator->setCategory($category);    
        }

        $tableGenerator->run();

        $output->writeln('Операция выполнена успешно.');
        return 1;
    }
}
