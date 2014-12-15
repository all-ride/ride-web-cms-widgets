<?php

namespace ride\web\cms\controller\widget;

use ride\library\validation\exception\ValidationException;

/**
 * Widget to add google analytics to your web page
 */
class GoogleAnalyticsWidget extends AbstractWidget {

    /**
     * Machine name of this widget
     * @var string
     */
    const NAME = 'google.analytics';

    /**
     * Path to the icon of this widget
     * @var string
     */
    const ICON = 'img/cms/widget/google-analytics.png';

    /**
     * Namespace for the templates of this widget
     * @var string
     */
    const TEMPLATE_NAMESPACE = 'cms/widget/google-analytics';

    /**
     * Sets the title view to the response
     * @return null
     */
    public function indexAction() {
        $code = $this->properties->getWidgetProperty('code');
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

        $code = $this->properties->getWidgetProperty('code');
        if ($code) {
            $preview .= '<strong>' . $translator->translate('label.code.google.analytics') . '</strong>:' . $code . '<br>';
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
            'code' => $this->properties->getWidgetProperty('code'),
            self::PROPERTY_TEMPLATE => $this->getTemplate(static::TEMPLATE_NAMESPACE . '/default'),
        );

        $form = $this->createFormBuilder($data);
        $form->addRow('code', 'string', array(
            'label' => $translator->translate('label.code.google.analytics'),
            'description' => $translator->translate('label.code.google.analytics.description'),
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

                $this->properties->setWidgetProperty('code', $data['code']);
                $this->setTemplate($data[self::PROPERTY_TEMPLATE]);

                return true;
            } catch (ValidationException $exception) {
                $this->setValidationException($exception, $form);
            }
        }

        $this->setTemplateView(static::TEMPLATE_NAMESPACE . '/properties', array(
            'form' => $form->getView(),
        ));
    }

}
