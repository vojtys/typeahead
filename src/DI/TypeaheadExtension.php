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
	private $defaults = [
		'limit' => 100,
		'minLength' => 2,
		'highlight' => true,
	];

	public function loadConfiguration(): void
	{
		$this->validateConfig($this->defaults);
	}

	public function afterCompile(Nette\PhpGenerator\ClassType $classType): void
	{
		$config = $this->getConfig();

		$initialize = $classType->getMethod('initialize');
		$initialize->addBody('Vojtys\Forms\Typeahead\TypeaheadExtension::bind(?);', [$config]);
	}

	public static function bind($config): void
	{
		Container::extensionMethod('addTypeahead',
			function ($container, $name, $label = null, $display = null, $remote = null) use ($config) {
				$typeaheadInput = new TypeaheadInput($label);

				$typeaheadInput->setConfig($config);
				$typeaheadInput->setRemote($remote);
				$typeaheadInput->setDisplay($display);

				return $container[$name] = $typeaheadInput;
			});
	}
}