(function($, window, document, location, navigator) {

    /* jshint laxbreak: true, expr: true */
    "use strict";

    // init objects
    var Vojtys = window.Vojtys || {};
    Vojtys.Forms = Vojtys.Forms || {};

    // init variables
    var engine, remoteHost, template, empty;

    $.fn.vojtysFormsTypeahead = function() {

        return this.each(function() {
            var $this = $(this);

            var settings = $.extend({}, $.fn.vojtysFormsTypeahead.defaults, $this.data('settings'));

            // init vojtys typeahead
            if (!$this.data('vojtys-forms-typeahead')) {
                $this.data('vojtys-forms-typeahead', (new Vojtys.Forms.Typeahead($this, settings)));
            }
        });
    };

    Vojtys.Forms.Typeahead = function($element, options) {

        var placeholder = $element.data('query-placeholder');
        var display = $element.data('display');
        var bh = new Bloodhound({
            datumTokenizer: Bloodhound.tokenizers.obj.whitespace,
            queryTokenizer: Bloodhound.tokenizers.whitespace,
            remote: {
                url: $element.data('remote-url'),
                wildcard: placeholder
            }
        });

        $element.typeahead(
            options,
            {
                display: display,
                source: bh,
            });

    };

    Vojtys.Forms.Typeahead.load = function() {
        $('[data-vojtys-forms-typeahead]').vojtysFormsTypeahead();
    };

    // autoload typeahead
    Vojtys.Forms.Typeahead.load();

    /**
     * Default settings
     */
    $.fn.vojtysFormsTypeahead.defaults = {};

    // assign to DOM
    window.Vojtys = Vojtys;

    // return Objects
    return Vojtys;

    // Immediately invoke function with default parameters
})(jQuery, window, document, location, navigator)
