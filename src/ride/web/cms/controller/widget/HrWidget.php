<?php

namespace ride\web\cms\controller\widget;

/**
 * Widget for a horizontal line
 */
class HrWidget extends AbstractWidget implements StyleWidget {

    /**
     * Machine name for this widget
     * @var string
     */
    const NAME = 'hr';

    /**
     * path to the icon for thid widget
     * @var string
     */
    const ICON = "img/cms/widget/hr.png";

    /**
     * path to the template of this widget
     * @var string
     */
    const TEMPLATE = 'cms/widget/hr';

    /**
     * Action to render this widget
     */
    public function indexAction() {
        $this->setTemplateView(self::TEMPLATE);
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
