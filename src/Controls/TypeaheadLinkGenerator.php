<?php declare(strict_types=1);

namespace Vojtys\Forms\Typeahead;

trait TypeaheadLinkGenerator
{
	public function link($presenter, $destination, $args = array()): string
	{
		$args = is_array($args) ? $args : array_slice(func_get_args(), 1);

		if (!(isset($destination[0]) && $destination[0] === ':')) {
			$path = $this->lookupPath('Nette\Application\UI\Presenter', TRUE);
			$a = strpos($destination, '//');
			if ($a !== FALSE) {
				$destination = substr($destination, 0, $a + 2) . $path . '-' . substr($destination, $a + 2);
			} else {
				$destination = $path . '-' . $destination;
			}
			$newArgs = [];
			foreach ($args as $key => $arg) {
				$newArgs[$path . '-' . $key] = $arg;
			}
			$args = $newArgs;
		}

		return $presenter->link($destination, $args);
	}
}