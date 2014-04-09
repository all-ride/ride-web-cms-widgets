<?php

namespace ride\web\cms\text\format;

use ride\web\cms\controller\widget\TextWidget;
use ride\web\cms\text\Text;

/**
 * Plain text format
 */
abstract class AbstractTextFormat implements TextFormat {

    /**
     * Gets the machine name of this format
     * @return string
     */
    public function getName() {
        return static::NAME;
    }

    /**
     * Gets the HTML of the provided text
     * @param string $text Text as edited by the user
     * @return string HTML version of the text
     */
    public function getHtml($text) {
        return $text;
    }

    /**
     * Updates the text with the submitted data
     * @param \ride\web\cms\text\Text $text Text to update
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
        $text->setFormat($this->getName());
    }

}
