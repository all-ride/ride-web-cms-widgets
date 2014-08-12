<?php

namespace ride\web\cms\controller\widget;

use ride\library\StringHelper;

/**
 * Widget to embed code from another site
 */
class EmbedWidget extends AbstractWidget implements StyleWidget {

    /**
     * Machine name of this widget
     * @var string
     */
    const NAME = 'embed';

    /**
     * Path to the icon of this widget
     * @var string
     */
    const ICON = 'img/cms/widget/embed.png';

    /**
     * Path to the template resource of this widget
     * @var string
     */
    const TEMPLATE = 'cms/widget/embed/index';

    /**
     * Name of the embed property
     * @var string
     */
    const PROPERTY_EMBED = 'embed';

    /**
     * Sets a title view to the response
     * @return null
     */
    public function indexAction() {
        $embed = $this->properties->getWidgetProperty(self::PROPERTY_EMBED);
        if (!$embed) {
            return;
        }

        $this->setTemplateView(self::TEMPLATE, array(
            'embed' => $embed,
        ));

        if ($this->properties->isAutoCache()) {
            $this->properties->setCache(true);
        }
    }

    /**
     * Get a preview of the properties of this widget
     * @return string
     */
    public function getPropertiesPreview() {
        $embed = $this->properties->getWidgetProperty(self::PROPERTY_EMBED);
        if (!$embed) {
            return '---';
        }

        return StringHelper::truncate(htmlentities($embed), 120);
    }

    /**
     * Action to handle and show the properties of this widget
     * @return null
     */
    public function propertiesAction() {
        $translator = $this->getTranslator();

        $data = array(
            self::PROPERTY_EMBED => $this->properties->getWidgetProperty(self::PROPERTY_EMBED),
        );

        $form = $this->createFormBuilder($data);
        $form->addRow(self::PROPERTY_EMBED, 'text', array(
            'label' => $translator->translate('label.embed'),
            'filters' => array(
                'trim' => array(),
            )
        ));

        $form = $form->build();
        if ($form->isSubmitted()) {
            if ($this->request->getBodyParameter('cancel')) {
                return false;
            }

            try {
                $form->validate();

                $data = $form->getData();

                $this->properties->setWidgetProperty(self::PROPERTY_EMBED, $data[self::PROPERTY_EMBED]);

                return true;
            } catch (ValidationException $exception) {
                $this->setValidationException($exception, $form);
            }
        }

        $this->setTemplateView('cms/widget/embed/properties', array(
            'form' => $form->getView(),
        ));

        return false;
    }

    /**
     * Gets the options for the styles
     * @return array Array with the name of the option as key and the
     * translation key as value
     */
    public function getWidgetStyleOptions() {
        return array(
            'container' => 'label.widget.style.container',
        );
    }

}
