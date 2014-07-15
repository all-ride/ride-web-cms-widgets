<?php

namespace ride\web\cms\form;

use ride\library\form\component\AbstractComponent;
use ride\library\form\FormBuilder;

/**
 * Form component for a call to action
 */
class CallToActionComponent extends AbstractComponent {

    /**
     * Available icons
     * @var array
     */
    protected $icons;

    /**
     * Available nodes
     * @var array
     */
    protected $nodes;

    /**
     * Sets available icons for the call to action button
     * @param array $icons Name of the icon as key, label as value
     * @return null
     */
    public function setIcons(array $icons) {
        $this->icons = $icons;
    }

    /**
     * Sets available nodes for the action URL
     * @param array $nodes Array with the id of the node as key, label for the
     * node as value
     * @return null
     */
    public function setNodes(array $nodes) {
        $this->nodes = $nodes;
    }

    /**
     * Prepares the form by adding row definitions
     * @param ride\library\form\FormBuilder $builder
     * @param array $options
     * @return null
     */
    public function prepareForm(FormBuilder $builder, array $options) {
        $translator = $options['translator'];

        $builder->addRow('id', 'hidden');
        if ($this->icons) {
            $builder->addRow('icon', 'select', array(
                'label' => $translator->translate('label.icon'),
                'options' => $this->icons,
            ));
        }
        $builder->addRow('label', 'string', array(
            'label' => $translator->translate('label.label'),
            'filters' => array(
                'trim' => array(),
            ),
            'validators' => array(
                'required' => array(),
            )
        ));
        if ($this->nodes) {
            $builder->addRow('node', 'select', array(
                'label' => $translator->translate('label.node'),
                'options' => $this->nodes,
            ));
        }
        $builder->addRow('url', 'string', array(
            'label' => $translator->translate('label.url'),
            'filters' => array(
                'trim' => array(),
            )
        ));
    }

}
