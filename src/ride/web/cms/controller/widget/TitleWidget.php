<?php

namespace ride\web\cms\controller\widget;

/**
 * Widget to show the name of the current node as title
 */
class TitleWidget extends AbstractWidget implements StyleWidget {

	/**
	 * Machine name of this widget
	 * @var string
	 */
    const NAME = 'title';

    /**
     * Path to the icon of this widget
     * @var string
     */
    const ICON = 'img/cms/widget/title.png';

    /**
     * Path to the template of the widget view
     * @var string
     */
    const TEMPLATE = 'cms/widget/title';

    /**
     * Sets a title view to the response
     * @return null
     */
    public function indexAction() {
        // title is being fetched from the context so no template variables needed
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
            'title' => 'label.widget.style.title',
        );
    }

}
