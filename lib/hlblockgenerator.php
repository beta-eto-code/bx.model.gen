<?php


namespace BxModelGen;

use BxModelGen\Interfaces\EntityGeneratorInterface;
use Nette\PhpGenerator\PhpNamespace;

class HlBlockGenerator implements EntityGeneratorInterface
{
    /**
     * @var string
     */
    private $type;
    /**
     * @var string
     */
    private $code;

    public function __construct(string $type, string $code)
    {
        $this->type = $type;
        $this->code = $code;

        //(new PhpNamespace(''))->addClass()
    }

    public function run()
    {
        // TODO: Implement makeModel() method.
    }
}