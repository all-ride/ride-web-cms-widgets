<?php

namespace ride\web\cms\text\io;

use ride\library\form\FormBuilder;
use ride\library\i18n\translator\Translator;

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
     * Processes the properties form to update the editor for this io
     * @param \ride\library\form\FormBuilder $formBuilder Form builder for the
     * text properties
     * @param \ride\library\i18n\translator\Translator $translator Instance of
     * the translator
     * @param string $locale Current locale
     * @param \ride\web\cms\text\Text $text
     * @return null
     */
    public function processForm(FormBuilder $formBuilder, Translator $translator, $locale, Text $text) {

    }

}
