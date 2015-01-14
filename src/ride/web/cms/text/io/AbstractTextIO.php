<?php

namespace ride\web\cms\text\io;

use ride\library\form\FormBuilder;
use ride\library\i18n\translator\Translator;
use ride\library\mvc\view\View;
use ride\library\widget\WidgetProperties;

use ride\web\cms\text\Text;

/**
 * Widget properties implementation for input/output of the text widget
 */
abstract class AbstractTextIO implements TextIO {

    /**
     * Gets the machine name of this IO
     * @return string
     */
    public function getName() {
        return static::NAME;
    }

    /**
     * Processes the properties form to update the editor for this IO
     * @param \ride\library\widget\WidgetProperties $widgetProperties Instance
     * of the widget properties
     * @param string $locale Current locale
     * @param \ride\library\i18n\translator\Translator $translator Instance of
     * the translator
     * @param \ride\web\cms\text\Text $text Instance of the text
     * @param \ride\library\form\FormBuilder $formBuilder Form builder for the
     * text properties
     * @return null
     */
    public function processForm(WidgetProperties $widgetProperties, $locale, Translator $translator, Text $text, FormBuilder $formBuilder) {

    }

    /**
     * Hook to process the form data
     * @param \ride\web\cms\text\Text $text Instance of the text
     * @param array $data Data to preset the form
     * @return null
     */
    public function processFormData(Text $text, array &$data) {

    }

    /**
     * Hook to process the form view
     * @param \ride\library\widget\WidgetProperties $widgetProperties Instance
     * of the widget properties
     * @param \ride\library\i18n\translator\Translator $translator Instance of
     * the translator
     * @param \ride\web\cms\text\Text $text Instance of the text
     * @param \ride\library\mvc\view\View $view Instance of the properties view
     * @return null
     */
    public function processFormView(WidgetProperties $widgetProperties, Translator $translator, Text $text, View $view) {

    }

}
