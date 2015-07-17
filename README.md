Typeahead
===============

Typeahead (twitter/typeahead.js) for Nette framework

# Install

```sh
$ composer require vojtys/typeahead:v0.3.0
```

# Configuration

## NEON - add extension

```yaml
extensions:
	typehead: Vojtys\Forms\Typeahead\TypeaheadExtension
```

# Usage

## Presenter/Control
```php

/**
 * @return Nette\Application\UI\Form
 */
public function createComponentTestForm()
{
    $form = new Nette\Application\UI\Form();

    $items = $this->items; // item service

    // name, label, display (input displayed value), suggestion callback
    $typeahead = $form->addTypeahead('foo', 'Typeahead', 'title', function($display, $q) use ($items) {
        return $items->searchBy([$display => $q]); // returns array result [title => 'foo', description => 'foo foo']
    });
    $typeahead->setPlaceholder('napiš něco'); // initial placeholder

    // add handlebars templates (http://handlebarsjs.com/)
    // suggestion template
    $typeahead->suggestionTemplate = function(Nette\Utils\Html $template) {
        $inner = Nette\Utils\Html::el('div')->setText('{{title}} – {{description}}');
        return $template->add($inner);
    };
    // empty template
    $typeahead->emptyTemplate = function(Nette\Utils\Html $template) {
        $inner = Nette\Utils\Html::el('div')->setText('nic tu neni');
        return $template->add($inner);
    };

    $form->addSubmit('ok', 'Odeslat');
    return $form;
}
```

## CSS

```html
<link rel="stylesheet" type="text/css" href="https://www.mydomain.com/vendor/vojtys/client-side/css/typeahead.css">
```

## JavaScript

Before `</body>` element.

```html
<!-- handlebars.js -->
<script src='https://www.mydomain.com/vendor/vojtys/client-side/js/handlebars-v3.0.3.js'></script>
<!-- typehead.js -->
<script src='https://www.mydomain.com/vendor/vojtys/client-side/js/typeahead.js'></script>
<script src='https://www.mydomain.com/vendor/vojtys/client-side/js/vojtys.typeahead.js'></script>
```


