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
     * Namespace for the templates of this widget
     * @var string
     */
    const TEMPLATE_NAMESPACE = 'cms/widget/embed';

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

       $this->setTemplateView($this->getTemplate(static::TEMPLATE_NAMESPACE . '/default'), array(
            'embed' => $embed,
        ));
    }

    /**
     * Get a preview of the properties of this widget
     * @return string
     */
    public function getPropertiesPreview() {
        $embed = $this->properties->getWidgetProperty(self::PROPERTY_EMBED);

        $translator = $this->getTranslator();
        $preview = '';

        $preview .= '<strong>' . $translator->translate('label.template') . '</strong>: ' . $this->getTemplate(static::TEMPLATE_NAMESPACE . '/default') . '<br>';
        $preview .= StringHelper::truncate(htmlentities($embed), 120) . '<br>';

        return $preview;
    }

    /**
     * Action to handle and show the properties of this widget
     * @return null
     */
    public function propertiesAction() {
        $translator = $this->getTranslator();

        $data = array(
            self::PROPERTY_EMBED => $this->properties->getWidgetProperty(self::PROPERTY_EMBED),
            self::PROPERTY_TEMPLATE => $this->getTemplate(static::TEMPLATE_NAMESPACE . '/default'),
        );

        $form = $this->createFormBuilder($data);
        $form->addRow(self::PROPERTY_EMBED, 'text', array(
            'label' => $translator->translate('label.embed'),
            'attributes' => array(
                'rows' => 10,
            ),
            'filters' => array(
                'trim' => array(),
            )
        ));
        $form->addRow(self::PROPERTY_TEMPLATE, 'select', array(
            'label' => $translator->translate('label.template'),
            'description' => $translator->translate('label.template.widget.description'),
            'options' => $this->getAvailableTemplates(static::TEMPLATE_NAMESPACE),
            'validators' => array(
                'required' => array(),
            ),
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

                $this->setTemplate($data[self::PROPERTY_TEMPLATE]);

                return true;
            } catch (ValidationException $exception) {
                $this->setValidationException($exception, $form);
            }
        }

        $this->setTemplateView(static::TEMPLATE_NAMESPACE . '/properties', array(
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
            'container' => 'label.style.container',
        );
    }

    /**
     * Gets whether this widget caches when auto cache is enabled
     * @return boolean
     */
    public function isAutoCache() {
        return true;
    }

}
