<?php

namespace ride\web\cms\controller\widget;

/**
 * Widget to show a plain template to act on context
 */
class TemplateWidget extends AbstractWidget {

    /**
     * Machine name of this widget
     * @var string
     */
    const NAME = 'template';

    /**
     * Path to the icon of this widget
     * @var string
     */
    const ICON = 'img/cms/widget/template.png';

    /**
     * Name of the template property
     * @var string
     */
    const PROPERTY_TEMPLATE = 'template';

    /**
     * Gets the templates of this widget
     * @return array
     */
    public function getTemplates() {
        return array($this->getTemplate());
    }

    /**
     * Sets a title view to the response
     * @return null
     */
    public function indexAction() {
        $this->setTemplateView($this->getTemplate());

        if ($this->properties->isAutoCache()) {
            $this->properties->setCache(true);
        }
    }

    /**
     * Get a preview of the properties of this widget
     * @return string
     */
    public function getPropertiesPreview() {
        $translator = $this->getTranslator();
        $template = $this->getTemplate();

        return $translator->translate('label.template') . ': ' . $template;
    }

    /**
     * Action to handle and show the properties of this widget
     * @return null
     */
    public function propertiesAction() {
        $translator = $this->getTranslator();

        $data = array(
            self::PROPERTY_TEMPLATE => $this->getTemplate(),
        );

        $form = $this->createFormBuilder($data);
        $form->addRow(self::PROPERTY_TEMPLATE, 'string', array(
            'label' => $translator->translate('label.template'),
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

                $this->properties->setWidgetProperty(self::PROPERTY_TEMPLATE, $data[self::PROPERTY_TEMPLATE]);

                return true;
            } catch (ValidationException $e) {

            }
        }

        $this->setTemplateView('cms/widget/template/properties', array(
            'form' => $form->getView(),
        ));

        return false;
    }

    /**
     * Gets the template for this widget
     * @return string Relative path to the template resource
     */
    protected function getTemplate() {
        return $this->properties->getWidgetProperty('template', 'cms/widget/template/index');
    }

}
