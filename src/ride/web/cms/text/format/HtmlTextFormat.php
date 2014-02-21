<?php

namespace ride\web\cms\text\format;

use ride\library\form\FormBuilder;
use ride\library\i18n\translator\Translator;

use ride\web\cms\controller\widget\TextWidget;
use ride\web\cms\text\Text;

/**
 * Plain HTML text format
 */
class HtmlTextFormat implements TextFormat {

    /**
     * Gets the HTML of the provided text
     * @param string $text Text as edited by the user
     * @return string HTML version of the text
     */
    public function getHtml($text) {
        return $text;
    }

    /**
     * Processes the properties form to update the editor for this format
     * @param ride\library\form\FormBuilder $formBuilder Form builder for the
     * text properties
     * @param ride\library\i18n\translator\Translator $translator Instance of
     * the translator
     * @param string $locale Current locale
     * @return null
     */
    public function processForm(FormBuilder $formBuilder, Translator $translator, $locale) {
        $formBuilder->addRow(TextWidget::PROPERTY_TEXT, 'text', array(
            'label' => $translator->translate('label.html'),
            'attributes' => array(
                'class' => 'html',
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

    /**
     * Updates the text with the submitted data
     * @param ride\web\cms\text\Text $text Text to update
     * @param array $data Submitted data
     * @return null
     */
    public function setText(Text $text, array $data) {
        if (isset($data[TextWidget::PROPERTY_TEXT])) {
            $data = $data[TextWidget::PROPERTY_TEXT];
        } else {
            $data = '';
        }

        $text->setText($data);
    }

}