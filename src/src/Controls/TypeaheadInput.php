<?php

namespace Vojtys\Forms\Typeahead;

use Nette;
use Nette\Utils;
use Nette\Forms\Form;
use Nette\Forms\Controls\BaseControl;
use Nette\Application\UI\ISignalReceiver;

/**
 * Class TypeaheadInput
 * @package Vojtys\Forms\Typeahead
 */
class TypeaheadInput extends BaseControl implements ISignalReceiver
{
    const QUERY_PLACEHOLDER = '__QUERY_PLACEHOLDER__';

    /** @var string  */
    protected $placeholder;

    /** @var callable */
    protected $remote;

    /** @var callable */
    protected $prefetch;

    /** @var  Nette\Application\IPresenter */
    protected $presenter;

    /** @var  string */
    protected $display;


    public function __construct($label, $config, $remote = NULL, $prefetch = NULL)
    {
        parent::__construct($label);

        $this->monitor('Nette\Application\UI\Presenter');
        $this->remote = $remote;
        $this->prefetch = $prefetch;

        if ($this->getTranslator() != NULL ) {
            $this->placeholder = $this->getTranslator()->translate($label);
        }
    }

    public function setDisplay($opt)
    {
        $this->display = $opt;
    }

    public function handleRemote($params)
    {
        if (!is_callable($this->remote)) {
            throw new Nette\InvalidStateException('Undefined Typehad callback.');
        }
        $q = (array_key_exists('q', $params)) ? $params['q'] : NULL;
        $this->presenter->sendJson(Nette\Utils\Callback::invokeArgs($this->remote, [$q]));
    }

    public function handlePrefetch()
    {
    }

    public function getControlSettings()
    {
        return ['highlight' => TRUE];
    }

    public function loadHttpData()
    {
        $this->setValue($this->getHttpData(Form::DATA_LINE));
    }


    protected function attached($component)
    {
        parent::attached($component);

        if ($component instanceof Nette\Application\IPresenter) {
            $this->presenter = $component;

            // build links
            $remote = $this->link($component, 'remote!',  ['q' => self::QUERY_PLACEHOLDER]);
            $prefetch = $this->link($component, 'prefetch!');


            $this->control->addAttributes([
                'data-vojtys-forms-typeahead' => '',
                'data-remote-url' => $remote,
                'data-prefetch-url' => $prefetch,
                'data-settings' => $this->getControlSettings(),
                'data-query-placeholder' => self::QUERY_PLACEHOLDER,
                'data-display' => $this->display,
                'placeholder' => $this->placeholder,
            ]);
        }
    }

    public function link($presenter, $destination, $args = array())
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

    public function signalReceived($signal)
    {
        $params = $this->presenter->popGlobalParameters($this->lookupPath('Nette\Application\UI\Presenter', TRUE));
        if ($signal === 'remote') {
            $this->handleRemote($params);
        }
    }

}