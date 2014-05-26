<?php

namespace ride\web\cms\controller\widget;

use ride\library\i18n\I18n;

/**
 * Widget to change the current locale
 */
class LanguageSelectWidget extends AbstractWidget implements StyleWidget {

	/**
	 * Machine name of this widget
	 * @var string
	 */
    const NAME = 'language.select';

    /**
     * Path to the icon of this widget
     * @var string
     */
    const ICON = 'img/cms/widget/language-select.png';

    /**
     * Path to the template of the widget view
     * @var string
     */
    const TEMPLATE = 'cms/widget/language.select';

    /**
     * Sets a title view to the response
     * @return null
     */
    public function indexAction(I18n $i18n) {
        $urls = array();

        $locales = $i18n->getLocales();
        $node = $this->properties->getNode();

        $content = $this->getContext('content');
        if (isset($content->type)) {
            $contentMapper = $this->getContentFacade()->getContentMapper($content->type);
            $site = $node->getRootNodeId();
        } else {
            $content = null;

            $url = $this->request->getUrl();
            $routeUrl = $this->getUrl($this->request->getRoute()->getId(), array(
                'node' => $node->getId(),
                'locale' => $this->locale,
            ));
            $suffix = str_replace($routeUrl, '', $url);
        }

        foreach ($locales as $localeCode => $locale) {
            if (!$node->isAvailableInLocale($localeCode)) {
                continue;
            }

            if ($content) {
                $urls[$localeCode] = array(
                    'url' => $contentMapper->getUrl($site, $localeCode, $content->data),
                    'locale' => $locale,
                );
            } else {
                $urls[$localeCode] = array(
                    'url' => $node->getUrl($localeCode, $this->request->getBaseScript()) . $suffix,
                    'locale' => $locale,
                );
            }
        }

        $this->setTemplateView(static::TEMPLATE, array(
        	'locales' => $urls,
        ));

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
