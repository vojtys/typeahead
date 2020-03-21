Typeahead
===============

Typeahead (twitter/typeahead.js) for Nette framework

# Install

```sh
$ composer require vojtys/typeahead
```

# Versions

| State  | Version      | Branch   | Nette  | PHP     |
|--------|--------------|----------|--------|---------|
| stable | `v0.4.1`     | `master` | `3.0+` | `>=7.1` |
| stable | `v0.3.3`     | `master` | `2.4`  | `>=5.6` |

# Configuration

```yaml
extensions:
	typehead: Vojtys\Forms\Typeahead\TypeaheadExtension

typeahead:
	limit: 10
	minLength: 2
	highlight: true
```

# Usage

## Presenter/Control
```php

public function createComponentTypeaheadForm(): Form
{
    $form = new Form();

    /** @var TypeaheadInput $typeahead */

    // name, label, display (input displayed value), suggestion callback
    $typeahead = $form->addTypeahead('typeahead', 'Typeahead', 'title', function($display, $q) {

        return $this->searchBy($q); // returns array result [title => 'foo', description => 'foo foo']
    });

    $typeahead->setPlaceholder('Začni psát...'); // initial placeholder

    // add handlebars templates (http://handlebarsjs.com/)

    // suggestion template
    $typeahead->setSuggestionTemplate(function(Html $template) {
        $inner = Html::el('div')->setText('{{title}} – {{description}}');

        return $template->addHtml($inner);
    });

    // empty template
    $typeahead->setNotFoundTemplate(function(Html $template) {
        $inner = Html::el('div')->setText('nic tu neni');

        return $template->addHtml($inner);
    });

    $form->addSubmit('ok', 'Odeslat');

    return $form;
}
```

## css

```html
<link rel="stylesheet" type="text/css" href="https://www.example.com/vendor/vojtys/assets/css/typeahead.css">
```

## js

Before `</body>` element.

```html
<!-- nette.ajax.js -->
<script src='https://www.example.com/vendor/vojtys/assets/js/nette.ajax.js'></script>
<!-- handlebars.js -->
<script src='https://www.example.com/vendor/vojtys/assets/js/handlebars.min-v4.7.3.js'></script>
<!-- typehead.js -->
<script src='https://www.example.com/vendor/vojtys/assets/js/typeahead.js'></script>
<script src='https://www.example.com/vendor/vojtys/assets/js/vojtys.typeahead.js'></script>
```


