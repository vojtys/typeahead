<?php declare(strict_types=1);

namespace Vojtys\Forms\Typeahead;

use Nette;
use Nette\Utils\Html;
use Nette\Forms\Form;
use Nette\Application\UI\ISignalReceiver;

/**
 * Class TypeaheadInput
 * @package Vojtys\Forms\Typeahead
 */
class TypeaheadInput extends Nette\Forms\Controls\BaseControl implements ISignalReceiver
{
	use TypeaheadLinkGenerator;

	const QUERY_PLACEHOLDER = '__QUERY_PLACEHOLDER__';

	/** @var Nette\Application\UI\Presenter $presenter */
	private $presenter;

	/** @var callable $suggestionTemplate */
	private $suggestionTemplate;

	/** @var callable $notFoundTemplate */
	private $notFoundTemplate;

	/** @var array $config */
	private $config;

	/** @var string $placeholder */
	private $placeholder;

	/** @var callable $remote */
	private $remote;

	/** @var  string $remoteLink */
	private $remoteLink;

	/** @var  string $display */
	private $display;


	public function __construct(string $label)
	{
		parent::__construct($label);

		// sets presenter and remote link
		$this->monitor(Nette\Application\UI\Presenter::class, function ($presenter): void {

			/** @var Nette\Application\UI\Presenter $presenter */
			$this->presenter = $presenter;

			$this->remoteLink = $this->link($presenter, 'remote!', [
				'q' => self::QUERY_PLACEHOLDER
			], 'link');
		});
	}

	public function getControl(): Html
	{
		$wrapper = Html::el('div');

		$input = parent::getControl();

		$input->addAttributes([
			'class' => 'form-control',
			'data-vojtys-forms-typeahead' => '',
			'data-remote-url' => $this->remoteLink,
			'data-settings' => $this->getControlSettings(),
			'data-query-placeholder' => self::QUERY_PLACEHOLDER,
			'data-display' => $this->display,
			'placeholder' => $this->placeholder,
			'value' => $this->getValue(),
		]);

		$wrapper->addHtml($input);

		if (!empty($this->suggestionTemplate)) {
			$template = $this->buildTemplate($this->suggestionTemplate, 'result-template');
			$wrapper->addHtml($template);
		}

		if (!empty($this->notFoundTemplate)) {
			$template = $this->buildTemplate($this->notFoundTemplate, 'empty-template');
			$wrapper->addHtml($template);
		}

		return $wrapper;
	}

	/**
	 * @param $params
	 * @throws Nette\Application\AbortException
	 */
	private function handleRemote($params)
	{
		$q = isset($params['q']) ? $params['q'] : null;

		$remote = $this->remote;

		// call remote function with displayed key and query
		$this->presenter->sendJson($remote($this->display, $q));
	}

	private function buildTemplate(callable $callback, $id): Html
	{
		$script = Html::el('script', ['id' => $id, 'type' => 'text/x-handlebars-template']);

		// create template
		$template = $callback(Html::el('div'));

		return $script->addHtml($template);
	}

	/**
	 * @throws Nette\Application\AbortException
	 */
	public function signalReceived(string $signal): void
	{
		$params = $this->presenter->popGlobalParameters($this->lookupPath('Nette\Application\UI\Presenter', TRUE));

		if ($signal === 'remote') {
			$this->handleRemote($params);
		}
	}

	public function loadHttpData(): void
	{
		$this->setValue($this->getHttpData(Form::DATA_LINE));
	}

	public function getRemote(): callable
	{
		return $this->remote;
	}

	public function setRemote(callable $remote): void
	{
		$this->remote = $remote;
	}

	public function getDisplay(): string
	{
		return $this->display;
	}

	public function setDisplay(string $display): void
	{
		$this->display = $display;
	}

	public function setPlaceholder(string $placeholder): void
	{
		$this->placeholder = $placeholder;
	}

	public function setMinLength(int $opt): void
	{
		$this->config['minLength'] = $opt;
	}

	public function setLimit(int $limit): void
	{
		$this->config['limit'] = $limit;
	}

	public function setHighLight(int $opt): void
	{
		$this->config['highlight'] = $opt;
	}

	public function setConfig(array $config): void
	{
		$this->config = $config;
	}

	public function setSuggestionTemplate(callable $suggestionTemplate): void
	{
		$this->suggestionTemplate = $suggestionTemplate;
	}

	public function setNotFoundTemplate(callable $notFoundTemplate): void
	{
		$this->notFoundTemplate = $notFoundTemplate;
	}

	private function getControlSettings(): array
	{
		return [
			'highlight' => $this->config['highlight'],
			'minLength' => $this->config['minLength'],
			'limit' => $this->config['limit'],
		];
	}
}