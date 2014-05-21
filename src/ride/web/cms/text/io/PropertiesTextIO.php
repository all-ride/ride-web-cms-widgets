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
            $widgetProperties->setWidgetProperty(TextWidget::PROPERTY_TITLE . '.' . $locale, $data[TextWidget::PROPERTY_TITLE]);
            $widgetProperties->setWidgetProperty(TextWidget::PROPERTY_BODY . '.' . $locale, $data[TextWidget::PROPERTY_BODY]);
            $widgetProperties->setWidgetProperty(str_replace('-', '.', TextWidget::PROPERTY_IMAGE) . '.' . $locale, $data[TextWidget::PROPERTY_IMAGE]);
            $widgetProperties->setWidgetProperty(str_replace('-', '.', TextWidget::PROPERTY_IMAGE_ALIGNMENT) . '.' . $locale, $data[TextWidget::PROPERTY_IMAGE_ALIGNMENT]);
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
        $text->setTitle($widgetProperties->getWidgetProperty(TextWidget::PROPERTY_TITLE . '.' . $locale));
        $text->setBody($widgetProperties->getWidgetProperty(TextWidget::PROPERTY_BODY . '.' . $locale));
        $text->setImage($widgetProperties->getWidgetProperty(str_replace('-', '.', TextWidget::PROPERTY_IMAGE) . '.' . $locale));
        $text->setImageAlignment($widgetProperties->getWidgetProperty(str_replace('-', '.', TextWidget::PROPERTY_IMAGE_ALIGNMENT) . '.' . $locale));

        return $text;
    }

}
