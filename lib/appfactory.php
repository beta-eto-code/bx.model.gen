<?php


namespace Bx\Model\Gen;

use Symfony\Component\Console\Application;
use Bx\Model\Gen\Commands\GenerateIblock;
use Bx\Model\Gen\Commands\GenerateHlBlock;
use Bx\Model\Gen\Commands\GenerateTable;
use Bx\Model\Gen\Commands\GenerateSection;

class AppFactory
{
    /**
     * @return Application
     */
    public static function create(): Application
    {
        $app = new Application('Bitrix model generator', '0.2.1');
        $app->add(new GenerateIblock());
        $app->add(new GenerateTable());
        $app->add(new GenerateHlBlock());
        $app->add(new GenerateSection());

        return $app;
    }
}