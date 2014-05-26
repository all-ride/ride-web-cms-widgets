<?php

namespace ride\web\cms\controller\widget;

/**
 * Widget to show a table of contents of the text on the page
 */
class TextTocWidget extends AbstractWidget implements StyleWidget {

    /**
     * Machine name of this widget
     * @var string
     */
    const NAME = 'text.toc';

    /**
     * Path to the icon of this widget
     * @var string
     */
    const ICON = 'img/cms/widget/text.toc.png';

    /**
     * Path to the template of the widget view
     * @var string
     */
    const TEMPLATE = 'cms/widget/text/text.toc';

    /**
     * Sets a text index view to the response
     * @return null
     */
    public function indexAction() {
        $view = $this->setTemplateView(static::TEMPLATE);
        $view->addJavascript('js/cms/text.toc.js');

        if ($this->properties->isAutoCache()) {
            $this->properties->setCache(true);
        }
    }

    /**
     * Gets the options for the styles
     * @return array Array with the name of the option as key and the
     * translation key as value
     */
    public function getWidgetStyleOptions() {
        return array(
            'container' => 'label.widget.style.container',
        );
    }

}
