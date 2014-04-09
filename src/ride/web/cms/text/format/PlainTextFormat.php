<?php

namespace ride\web\cms\text\format;

use ride\library\form\FormBuilder;
use ride\library\i18n\translator\Translator;

use ride\web\cms\controller\widget\TextWidget;

/**
 * Plain text format
 */
class PlainTextFormat extends AbstractTextFormat {

    /**
     * Machine name of this format
     * @var string
     */
    const NAME = 'plain';

    /**
     * Processes the properties form to update the editor for this format
     * @param \ride\library\form\FormBuilder $formBuilder Form builder for the
     * text properties
     * @param \ride\library\i18n\translator\Translator $translator Instance of
     * the translator
     * @param string $locale Current locale
     * @return null
     */
    public function processForm(FormBuilder $formBuilder, Translator $translator, $locale) {
        $formBuilder->addRow(TextWidget::PROPERTY_TEXT, 'text', array(
            'label' => $translator->translate('label.text'),
            'attributes' => array(
                'rows' => '12',
            ),
            'filters' => array(
                'trim' => array(),
            ),
            'validators' => array(
                'required' => array(),
            )
        ));
    }

}
