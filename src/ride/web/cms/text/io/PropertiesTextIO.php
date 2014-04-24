<?php

namespace ride\web\cms\text\io;

use ride\library\widget\WidgetProperties;

use ride\web\cms\controller\widget\TextWidget;
use ride\web\cms\text\GenericText;
use ride\web\cms\text\Text;

/**
 * Widget properties implementation for input/output of the text widget
 */
class PropertiesTextIO extends AbstractTextIO {

    /**
     * Machine name of this IO
     * @var string
     */
    const NAME = 'properties';

    /**
     * Stores the text in the data source
     * @param \ride\library\widget\WidgetProperties $widgetProperties Instance
     * of the widget properties
     * @param string|array $locales Code of the current locale
     * @param \ride\web\cms\text\Text $text Instance of the text
     * @param array $data Submitted data
     * @return null
     */
    public function setText(WidgetProperties $widgetProperties, $locales, Text $text, array $data) {
        if (!is_array($locales)) {
            $locales = array($locales);
        }

        foreach ($locales as $locale) {
            $widgetProperties->setWidgetProperty(TextWidget::PROPERTY_FORMAT . '.' . $locale, $text->getFormat());
            $widgetProperties->setWidgetProperty(TextWidget::PROPERTY_TEXT . '.' . $locale, $text->getText());
        }
    }

    /**
     * Gets the text from the data source
     * @param \ride\library\widget\WidgetProperties $widgetProperties Instance
     * of the widget properties
     * @param string $locale Code of the current locale
     * @return \ride\web\cms\text\Text Instance of the text
     */
    public function getText(WidgetProperties $widgetProperties, $locale) {
        $text = new GenericText();
        $text->setFormat($widgetProperties->getWidgetProperty(TextWidget::PROPERTY_FORMAT . '.' . $locale));
        $text->setText($widgetProperties->getWidgetProperty(TextWidget::PROPERTY_TEXT . '.' . $locale));

        return $text;
    }

}
