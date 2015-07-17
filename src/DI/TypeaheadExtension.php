<?php

namespace Vojtys\Forms\Typeahead;

use Nette;
use Nette\Forms\Container;

/**
 * Class TypeaheadExtension
 * @package Vojtys\Forms\Typeahead
 */
class TypeaheadExtension extends Nette\DI\CompilerExtension
{
    protected $config = [];

    /**
     * @param Nette\PhpGenerator\ClassType $classType
     */
    public function afterCompile(Nette\PhpGenerator\ClassType $classType)
    {
        $config = $this->getConfig($this->config);
        $initialize = $classType->getMethod('initialize');
        $initialize->addBody('Vojtys\Forms\Typeahead\TypeaheadExtension::bind(?);', [$config]);
    }

    /**
     * @param array $config
     */
    public static function bind($config)
    {
        Container::extensionMethod('addTypeahead', function($container, $name, $label = NULL, $display = NULL, $remote = NULL) use ($config) {
            return $container[ $name ] = new TypeaheadInput($label, $config, $display, $remote);
        });
    }
}