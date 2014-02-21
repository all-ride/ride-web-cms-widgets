<?php

namespace ride\web\cms\text\format;

use ride\library\form\FormBuilder;
use ride\library\i18n\translator\Translator;

use ride\web\cms\text\Text;

/**
 * Interface for a text format
 */
interface TextFormat {

    /**
     * Gets the HTML of the provided text
     * @param string $text Text as edited by the user
     * @return string HTML version of the text
     */
    public function getHtml($text);

    /**
     * Processes the properties form to update the editor for this format
     * @param ride\library\form\FormBuilder $formBuilder Form builder for the
     * text properties
     * @param ride\library\i18n\translator\Translator $translator Instance of
     * the translator
     * @param string $locale Current locale
     * @return null
     */
    public function processForm(FormBuilder $formBuilder, Translator $translator, $locale);

    /**
     * Updates the text with the submitted data
     * @param ride\web\cms\text\Text $text Text to update
     * @param array $data Submitted data
     * @return null
     */
    public function setText(Text $text, array $data);

}