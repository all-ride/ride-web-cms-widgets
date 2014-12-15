<?php

namespace ride\web\cms\controller\widget;

/**
 * Widget to show the name of the current node as title
 */
class TitleWidget extends AbstractWidget implements StyleWidget {

	/**
	 * Machine name of this widget
	 * @var string
	 */
    const NAME = 'title';

    /**
     * Path to the icon of this widget
     * @var string
     */
    const ICON = 'img/cms/widget/title.png';

    /**
     * Namespace for the templates of this widget
     * @var string
     */
    const TEMPLATE_NAMESPACE = 'cms/widget/title';

    /**
     * Sets a title view to the response
     * @return null
     */
    public function indexAction() {
        // title is being fetched from the context so no template variables needed
        $this->setTemplateView($this->getTemplate(static::TEMPLATE_NAMESPACE . '/default'));

    	if ($this->properties->isAutoCache()) {
    	    $this->properties->setCache(true);
    	}
    }

    /**
     * Gets a preview of the properties of this widget instance
     * @return string
     */
    public function getPropertiesPreview() {
        $translator = $this->getTranslator();

        $preview = '<strong>' . $translator->translate('label.template') . '</strong>: ' . $this->getTemplate(static::TEMPLATE_NAMESPACE . '/default') . '<br />';

        return $preview;
    }

    /**
     * Action to handle and show the properties of this widget
     * @return null
     */
    public function propertiesAction() {
        $translator = $this->getTranslator();

        $data = array(
            self::PROPERTY_TEMPLATE => $this->getTemplate(static::TEMPLATE_NAMESPACE . '/default'),
        );

        $form = $this->createFormBuilder($data);
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
            'title' => 'label.style.title',
        );
    }

}
