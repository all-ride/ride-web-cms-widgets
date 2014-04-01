<?php

namespace ride\web\cms\controller\widget;


/**
 * Widget to show the breadcrums of the current page
 */
class BreadcrumbsWidget extends AbstractWidget implements StyleWidget {

	/**
	 * Machine name of this widget
	 * @var string
	 */
    const NAME = 'breadcrumbs';

    /**
     * Path to the icon of this widget
     * @var string
     */
    const ICON = 'img/cms/widget/breadcrumbs.png';

    /**
     * Path to the template of the widget view
     * @var string
     */
    const TEMPLATE = 'cms/widget/breadcrumbs';

    /**
     * Sets a title view to the response
     * @return null
     */
    public function indexAction() {
        $this->setTemplateView(self::TEMPLATE);

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
            'menu' => 'label.widget.style.menu',
        );
    }

}
