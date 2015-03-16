<?php

namespace ride\web\cms\text\io;

use ride\library\cms\exception\CmsException;
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
            $widgetProperties->setLocalizedWidgetProperty($locale, TextWidget::PROPERTY_FORMAT, $text->getFormat());
            $widgetProperties->setLocalizedWidgetProperty($locale, TextWidget::PROPERTY_TITLE, $data[TextWidget::PROPERTY_TITLE]);
            $widgetProperties->setLocalizedWidgetProperty($locale, TextWidget::PROPERTY_SUBTITLE, $data[TextWidget::PROPERTY_SUBTITLE]);
            $widgetProperties->setLocalizedWidgetProperty($locale, TextWidget::PROPERTY_BODY, $data[TextWidget::PROPERTY_BODY]);
            $widgetProperties->setLocalizedWidgetProperty($locale, str_replace('-', '.', TextWidget::PROPERTY_IMAGE), $data[TextWidget::PROPERTY_IMAGE]);
            $widgetProperties->setLocalizedWidgetProperty($locale, str_replace('-', '.', TextWidget::PROPERTY_IMAGE_ALIGNMENT), $data[TextWidget::PROPERTY_IMAGE_ALIGNMENT]);

            $widgetProperties->clearWidgetProperties(TextWidget::PROPERTY_CTA . '.' . $locale);

            $index = 1;
            foreach ($data[TextWidget::PROPERTY_CTA] as $cta) {
                $widgetProperties->setWidgetProperty(TextWidget::PROPERTY_CTA . '.' . $locale . '.' . $index . '.label', $cta['label']);
                $widgetProperties->setWidgetProperty(TextWidget::PROPERTY_CTA . '.' . $locale . '.' . $index . '.node', $cta['node']);
                $widgetProperties->setWidgetProperty(TextWidget::PROPERTY_CTA . '.' . $locale . '.' . $index . '.url', $cta['url']);
                $widgetProperties->setWidgetProperty(TextWidget::PROPERTY_CTA . '.' . $locale . '.' . $index . '.type', $cta['type']);

                $index++;
            }
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
        $callToActions = array();

        $widgetProperties->getWidgetProperties(TextWidget::PROPERTY_CTA . '.' . $locale);
        foreach ($widgetProperties as $key => $value) {
            $keyTokens = explode('.', $key);
            if (count($keyTokens) < 3) {
                continue;
            }

            if (!isset($callToActions[$keyTokens[2]])) {
                $callToActions[$keyTokens[2]] = new GenericCallToAction();
            }

            $method = 'set' . ucfirst($keyTokens[3]);

            $callToActions[$keyTokens[2]]->$method($value);
        }

        $text = new GenericText();
        $text->setFormat($widgetProperties->getLocalizedWidgetProperty($locale, TextWidget::PROPERTY_FORMAT));
        $text->setTitle($widgetProperties->getLocalizedWidgetProperty($locale, TextWidget::PROPERTY_TITLE));
        $text->setSubtitle($widgetProperties->getLocalizedWidgetProperty($locale, TextWidget::PROPERTY_SUBTITLE));
        $text->setBody($widgetProperties->getLocalizedWidgetProperty($locale, TextWidget::PROPERTY_BODY));
        $text->setImage($widgetProperties->getLocalizedWidgetProperty($locale, str_replace('-', '.', TextWidget::PROPERTY_IMAGE)));
        $text->setImageAlignment($widgetProperties->getLocalizedWidgetProperty($locale, str_replace('-', '.', TextWidget::PROPERTY_IMAGE_ALIGNMENT)));
        $text->setCallToActions($callToActions);

        return $text;
    }

    /**
     * Gets an existing text from the data source
     * @param \ride\library\widget\WidgetProperties $widgetProperties Instance
     * of the widget properties
     * @param string $locale Code of the current locale
     * @param string $text Identifier of the text
     * @param boolean $isNew Flag to see if this text will be a new text
     * @return \ride\web\cms\text\Text Instance of the text
     */
    public function getExistingText(WidgetProperties $widgetProperties, $locale, $text, $isNew) {
        throw new CmsException('Existing text is not supported by the properties text IO');
    }

}
