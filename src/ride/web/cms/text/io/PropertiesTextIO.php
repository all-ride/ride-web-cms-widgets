<?php

namespace ride\web\cms\text\io;

use ride\library\form\FormBuilder;
use ride\library\i18n\translator\Translator;
use ride\library\widget\WidgetProperties;

use ride\web\cms\controller\widget\TextWidget;
use ride\web\cms\text\GenericText;
use ride\web\cms\text\Text;

/**
 * Widget properties implementation for input/output of the text widget
 */
class PropertiesTextIO implements TextIO {

    /**
     * Processes the properties form to update the editor for this io
     * @param ride\library\form\FormBuilder $formBuilder Form builder for the
     * text properties
     * @param ride\library\i18n\translator\Translator $translator Instance of
     * the translator
     * @param string $locale Current locale
     * @param ride\web\cms\text\Text $text
     * @return null
     */
    public function processForm(FormBuilder $formBuilder, Translator $translator, $locale, Text $text) {

    }

    /**
     * Stores the text in the data source
     * @param ride\library\widget\WidgetProperties $widgetProperties Instance
     * of the widget properties
     * @param string|array $locale Code of the current locale
     * @param ride\web\cms\text\Text $text Instance of the text
     * @param array $data Submitted data
     * @return null
     */
    public function setText(WidgetProperties $widgetProperties, $locale, Text $text, array $data) {
        if (!is_array($locale)) {
            $locale = array($locale);
        }

        foreach ($locale as $l) {
            $widgetProperties->setWidgetProperty(TextWidget::PROPERTY_FORMAT . '.' . $l, $text->getFormat());
            $widgetProperties->setWidgetProperty(TextWidget::PROPERTY_TEXT . '.' . $l, $text->getText());
        }
    }

    /**
     * Gets the text from the data source
     * @param ride\library\widget\WidgetProperties $widgetProperties Instance
     * of the widget properties
     * @param string $locale Code of the current locale
     * @return ride\web\cms\text\Text Instance of the text
     */
    public function getText(WidgetProperties $widgetProperties, $locale) {
        $text = new GenericText();
        $text->setFormat($widgetProperties->getWidgetProperty(TextWidget::PROPERTY_FORMAT . '.' . $locale));
        $text->setText($widgetProperties->getWidgetProperty(TextWidget::PROPERTY_TEXT . '.' . $locale));

        return $text;
    }

}