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
    const LIMIT = 10;
    const MIN_LENGTH = 2;

    /** @var string  */
    protected $placeholder;

    /** @var callable */
    protected $remote;

    /** @var  string */
    protected $remoteLink;

    /** @var callable */
    protected $prefetch;

    /** @var  Nette\Application\IPresenter */
    protected $presenter;

    /** @var  string */
    protected $display;

    /** @var int  */
    protected $limit = self::LIMIT;

    /** @var int  */
    protected $minLength = self::MIN_LENGTH;

    /** @var  callback */
    public $suggestionTemplate;

    /** @var  callback */
    public $emptyTemplate;


    /**
     * @param null $label
     * @param $config
     * @param null $display
     * @param null $remote
     */
    public function __construct($label, $config, $display = NULL, $remote = NULL)
    {
        parent::__construct($label);

        $this->monitor('Nette\Application\UI\Presenter');
        $this->remote = $remote;
        $this->display = $display;
    }

    protected function buildTemplate($callback, $id)
    {
        if (!is_callable($callback)) {
            return NULL;
        }

        $script = Utils\Html::el('script', ['id' => $id, 'type'=> 'text/x-handlebars-template']);
        $template = Nette\Utils\Callback::invokeArgs($callback, [Utils\Html::el('div')]);

        if (!$template instanceof Utils\Html) {
            throw new  Nette\InvalidArgumentException('Return value must be instance of Nette\Utils\Html');
        }

        return $script->add($template);
    }

    /**
     * @param $params
     * @throws \Nette\InvalidStateException
     */
    public function handleRemote($params)
    {
        if (!is_callable($this->remote)) {
            throw new Nette\InvalidStateException('Undefined Typehad callback.');
        }
        $q = (array_key_exists('q', $params)) ? $params['q'] : NULL;

        // call remote function with displayed key and query
        $this->presenter->sendJson(Nette\Utils\Callback::invokeArgs($this->remote, [$this->display, $q]));
    }

    /**
     * @return array
     */
    public function getControlSettings()
    {
        return ['highlight' => TRUE,
            'minLength' => $this->minLength,
            'limit' => $this->limit];
    }

    public function loadHttpData()
    {
        $this->setValue($this->getHttpData(Form::DATA_LINE));
    }

    public function getControl()
    {
        $wrapper = Utils\Html::el('div');
        $input = parent::getControl();

        $input->addAttributes([
            'class' => 'form-control',
            'data-vojtys-forms-typeahead' => '',
            'data-remote-url' => $this->remoteLink,
            'data-settings' => $this->getControlSettings(),
            'data-query-placeholder' => self::QUERY_PLACEHOLDER,
            'data-display' => $this->display,
            'placeholder' => $this->placeholder,
        ]);

        $wrapper->add($input);

        // suggestion template
        $st = $this->buildTemplate($this->suggestionTemplate, 'result-template');
        if (!empty($st)) {
            $wrapper->add($st);
        }

        // empty template
        $et = $this->buildTemplate($this->emptyTemplate, 'empty-template');
        if (!empty($et)) {
            $wrapper->add($et);
        }

        return $wrapper;
    }

    /**
     * @param $component
     */
    protected function attached($component)
    {
        parent::attached($component);

        if ($component instanceof Nette\Application\IPresenter) {
            $this->presenter = $component;

            // build links
            $this->remoteLink = $this->link($component, 'remote!',  ['q' => self::QUERY_PLACEHOLDER]);
        }
    }

    /**
     * @param $placeholder
     * @return $this
     */
    public function setPlaceholder($placeholder)
    {
        $this->placeholder = $placeholder;
        return $this;
    }

    /**
     * @param $opt
     * @return $this
     */
    public function setMinLength($opt)
    {
        $this->minLength = $opt;
        return $this;
    }

    /**
     * @param $limit
     * @return $this
     */
    public function setLimit($limit)
    {
        $this->limit = $limit;
        return $this;
    }

    /**
     * @param $presenter
     * @param $destination
     * @param array $args
     * @author dg
     *
     * @return mixed
     */
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

    /**
     * @param $signal
     */
    public function signalReceived($signal)
    {
        $params = $this->presenter->popGlobalParameters($this->lookupPath('Nette\Application\UI\Presenter', TRUE));
        if ($signal === 'remote') {
            $this->handleRemote($params);
        }
    }

}