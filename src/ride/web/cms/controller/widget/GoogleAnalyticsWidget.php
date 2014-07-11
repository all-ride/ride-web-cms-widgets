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
     * Path to the template of this widget
     * @var string
     */
    const TEMPLATE = 'cms/widget/google/analytics';

    /**
     * Sets the title view to the response
     * @return null
     */
    public function indexAction() {
        $code = $this->properties->getWidgetProperty('code');
        if (!$code) {
            return;
        }

        $this->setTemplateView(self::TEMPLATE, array(
            'code' => $code,
        ));
    }

    /**
     * Get a preview of the properties of this widget
     * @return string
     */
    public function getPropertiesPreview() {
        $translator = $this->getTranslator();

        $code = $this->properties->getWidgetProperty('code');
        if ($code) {
            $preview = '<strong>' . $translator->translate('label.code.google.analytics') . '</strong>:' . $code . '<br/>>';
        } else {
            $preview = '---';
        }

        return $preview;
    }


    /**
     * Action to handle and show the properties of this widget
     * @return null
     */
    public function propertiesAction() {
        $translator = $this->getTranslator();

        $data = array(
            'code' => $this->properties->getWidgetProperty('code')
        );

        $form = $this->createFormBuilder($data);
        $form->addRow('code', 'string', array(
            'label' => $translator->translate('label.code.google.analytics'),
            'description' => $translator->translate('label.code.google.analytics.description'),
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

                return true;
            } catch (ValidationException $exception) {
                $this->setValidationException($exception, $form);
            }
        }

        $this->setTemplateView('cms/widget/google/properties', array(
            'form' => $form->getView(),
        ));
    }

}
