<?php

namespace ride\web\cms\controller\widget;

use ride\library\StringHelper;

/**
 * Widget to table code from another site
 */
class TableWidget extends AbstractWidget implements StyleWidget {

    /**
     * Machine name of this widget
     * @var string
     */
    const NAME = 'table';

    /**
     * Path to the icon of this widget
     * @var string
     */
    const ICON = 'img/cms/widget/table.svg';

    /**
     * Namespace for the templates of this widget
     * @var string
     */
    const TEMPLATE_NAMESPACE = 'cms/widget/table';

    /**
     * Name of the table property
     * @var string
     */
    const PROPERTY_TABLE = 'table';

    /**
     * Name of the row header property
     * @var string
     */
    const PROPERTY_ROW_HEADER = 'rowHeader';

    const PROPERTY_DIMENSIONS_X = 'dimensionsX';
    const PROPERTY_DIMENSIONS_Y = 'dimensionsY';

    /**
     * Sets a title view to the response
     * @return null
     */
    public function indexAction() {
        $table = $this->properties->getWidgetProperty(self::PROPERTY_TABLE);
        $rowHeader = $this->properties->getWidgetProperty(self::PROPERTY_ROW_HEADER, true);
        if (!$table) {
            return;
        }

       $this->setTemplateView($this->getTemplate(static::TEMPLATE_NAMESPACE . '/default'), array(
            'table' => $table,
            'rowHeader' => $rowHeader,
        ));
    }

    /**
     * Get a preview of the properties of this widget
     * @return string
     */
    public function getPropertiesPreview() {
        $translator = $this->getTranslator();
        $preview = '';

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
            self::PROPERTY_TABLE => $this->properties->getWidgetProperty(self::PROPERTY_TABLE),
            self::PROPERTY_ROW_HEADER => $this->properties->getWidgetProperty(self::PROPERTY_ROW_HEADER, true),
            self::PROPERTY_DIMENSIONS_X => $this->properties->getWidgetProperty(self::PROPERTY_DIMENSIONS_Y),
            self::PROPERTY_DIMENSIONS_Y => $this->properties->getWidgetProperty(self::PROPERTY_DIMENSIONS_Y),
            self::PROPERTY_TEMPLATE => $this->getTemplate(static::TEMPLATE_NAMESPACE . '/default'),
        );

        $form = $this->createFormBuilder($data);
        $form->addRow(self::PROPERTY_TABLE, 'hidden', array(
            'filters' => array(
                'trim' => array(),
            )
        ));
        $form->addRow(self::PROPERTY_ROW_HEADER, 'boolean', array(
            'label' => $translator->translate('label.table.row.header'),
            'description' => $translator->translate('label.table.row.header.description'),
        ));
        $form->addRow(self::PROPERTY_DIMENSIONS_X, 'integer', array(
            'label' => $translator->translate('label.table.dimensions.x'),
        ));
        $form->addRow(self::PROPERTY_DIMENSIONS_Y, 'integer', array(
            'label' => $translator->translate('label.table.dimensions.y'),
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

                $this->properties->setWidgetProperty(self::PROPERTY_TABLE, json_encode($_POST['tableText']));
                $this->properties->setWidgetProperty(self::PROPERTY_ROW_HEADER, $data[self::PROPERTY_ROW_HEADER]);
                $this->properties->setWidgetProperty(self::PROPERTY_DIMENSIONS_X, $data[self::PROPERTY_DIMENSIONS_X]);
                $this->properties->setWidgetProperty(self::PROPERTY_DIMENSIONS_Y, $data[self::PROPERTY_DIMENSIONS_Y]);

                $this->setTemplate($data[self::PROPERTY_TEMPLATE]);

                return true;
            } catch (ValidationException $exception) {
                $this->setValidationException($exception, $form);
            }
        }

        $view = $this->setTemplateView(static::TEMPLATE_NAMESPACE . '/properties', array(
            'form' => $form->getView(),
        ));
        $view->addJavascript('js/cms/table.widget.js');
        $form->processView($view);

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
