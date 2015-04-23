<?php

namespace ride\web\cms\controller\widget;

use ride\library\validation\exception\ValidationException;

/**
 * Widget to add google analytics to your web page
 */
class GoogleTagsWidget extends AbstractWidget {

    /**
     * Machine name of this widget
     * @var string
     */
    const NAME = 'google.tags';

    /**
     * Path to the icon of this widget
     * @var string
     */
    const ICON = 'img/cms/widget/google-tags.png';

    /**
     * Name of the code property
     * @var string
     */
    const PROPERTY_CODE = 'code';

    /**
     * Namespace for the templates of this widget
     * @var string
     */
    const TEMPLATE_NAMESPACE = 'cms/widget/google-tags';

    /**
     * Sets the title view to the response
     * @return null
     */
    public function indexAction() {
        $code = $this->properties->getWidgetProperty(self::PROPERTY_CODE);
        if (!$code) {
            return;
        }

        $this->setTemplateView($this->getTemplate(static::TEMPLATE_NAMESPACE . '/default'), array(
            'code' => $code,
        ));
    }

    /**
     * Get a preview of the properties of this widget
     * @return string
     */
    public function getPropertiesPreview() {
        $translator = $this->getTranslator();
        $preview = '';

        $code = $this->properties->getWidgetProperty(self::PROPERTY_CODE);
        if ($code) {
            $preview .= '<strong>' . $translator->translate('label.code.google.tags') . '</strong>:' . $code . '<br>';
        }

        $preview .= '<strong>' . $translator->translate('label.template') . '</strong>: ' . $this->getTemplate(static::TEMPLATE_NAMESPACE . '/default') . '<br>';

        return $preview;
    }

    /**
     * Action to handle and show the properties of this widget
     * @return null
     */
    public function propertiesAction() {
        $translator = $this->getTranslator();

        $data = array(
            self::PROPERTY_CODE => $this->properties->getWidgetProperty(self::PROPERTY_CODE),
            self::PROPERTY_TEMPLATE => $this->getTemplate(static::TEMPLATE_NAMESPACE . '/default'),
        );

        $form = $this->createFormBuilder($data);
        $form->addRow(self::PROPERTY_CODE, 'string', array(
            'label' => $translator->translate('label.code.google.tags'),
            'description' => $translator->translate('label.code.google.tags.description'),
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

                $this->properties->setWidgetProperty(self::PROPERTY_CODE, $data[self::PROPERTY_CODE]);

                return true;
            } catch (ValidationException $exception) {
                $this->setValidationException($exception, $form);
            }
        }

        $this->setTemplateView(static::TEMPLATE_NAMESPACE . '/properties', array(
            'form' => $form->getView(),
        ));
    }

    /**
     * Gets whether this widget caches when auto cache is enabled
     * @return boolean
     */
    public function isAutoCache() {
        return true;
    }

}
