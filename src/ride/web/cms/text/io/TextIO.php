<?php

namespace ride\web\cms\text\io;

use ride\library\form\FormBuilder;
use ride\library\i18n\translator\Translator;
use ride\library\widget\WidgetProperties;

use ride\web\cms\text\Text;

/**
 * Interface for input/output of the text widget
 */
interface TextIO {

    /**
     * Gets the machine name of this IO
     * @return string
     */
    public function getName();

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
    public function processForm(WidgetProperties $widgetProperties, $locale, Translator $translator, Text $text, FormBuilder $formBuilder);

    /**
     * Hook to process the form data
     * @param \ride\web\cms\text\Text $text Instance of the text
     * @param array $data Data to preset the form
     * @return null
     */
    public function processFormData(Text $text, array &$data);

    /**
     * Stores the text in the data source
     * @param \ride\library\widget\WidgetProperties $widgetProperties Instance
     * of the widget properties
     * @param string|array $locale Code of the current locale
     * @param \ride\web\cms\text\Text $text Instance of the text
     * @param array $data Submitted data
     * @return null
     */
    public function setText(WidgetProperties $widgetProperties, $locale, Text $text, array $data);

    /**
     * Gets the text from the data source
     * @param \ride\library\widget\WidgetProperties $widgetProperties Instance
     * of the widget properties
     * @param string $locale Code of the current locale
     * @return \ride\web\cms\text\Text Instance of the text
     */
    public function getText(WidgetProperties $widgetProperties, $locale);

    /**
     * Gets a existing text from the data source
     * @param \ride\library\widget\WidgetProperties $widgetProperties Instance
     * of the widget properties
     * @param string $locale Code of the current locale
     * @param string $text Identifier of the text
     * @param boolean $isNew Flag to see if this text will be a new text
     * @return \ride\web\cms\text\Text Instance of the text
     */
    public function getExistingText(WidgetProperties $widgetProperties, $locale, $text, $isNew);

}
