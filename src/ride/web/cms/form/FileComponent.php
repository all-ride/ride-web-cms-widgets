<?php

namespace ride\web\cms\form;

use ride\library\form\component\AbstractComponent;
use ride\library\form\FormBuilder;

/**
 * Form component for a item of the files widget
 */
class FileComponent extends AbstractComponent {

    /**
     * Prepares the form by adding row definitions
     * @param ride\library\form\FormBuilder $builder
     * @param array $options
     * @return null
     */
    public function prepareForm(FormBuilder $builder, array $options) {
        $translator = $options['translator'];

        $builder->addRow('file', 'file', array(
            'label' => $translator->translate('label.file'),
            'validators' => array(
                'required' => array(),
            ),
        ));
        $builder->addRow('label', 'string', array(
            'label' => $translator->translate('label.label'),
            'filters' => array(
                'trim' => array(),
            ),
        ));
        $builder->addRow('image', 'image', array(
            'label' => $translator->translate('label.image'),
        ));
    }

}
