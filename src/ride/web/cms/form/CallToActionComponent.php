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
    protected $types;

    /**
     * Available nodes
     * @var array
     */
    protected $nodes;

    /**
     * Sets available types for the call to action button
     * @param array $types Name of the type as key, label as value
     * @return null
     */
    public function setTypes(array $types) {
        $this->types = $types;
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
        $builder->addRow('suffix', 'string', array(
            'label' => $translator->translate('label.url.suffix'),
            'description' => $translator->translate('label.url.suffix.description'),
            'filters' => array(
                'trim' => array(),
            )
        ));
        if ($this->types) {
            $builder->addRow('type', 'select', array(
                'label' => $translator->translate('label.type'),
                'options' => $this->types,
            ));
        }
    }

}
