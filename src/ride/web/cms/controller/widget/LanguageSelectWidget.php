<?php

namespace ride\web\cms\controller\widget;

use ride\library\cms\node\Node;
use ride\library\cms\Cms;
use ride\library\i18n\I18n;
use ride\library\router\exception\RouterException;

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
     * Namespace for the templates of this widget
     * @var string
     */
    const TEMPLATE_NAMESPACE = 'cms/widget/language-select';

    /**
     * Sets a title view to the response
     * @return null
     */
    public function indexAction(Cms $cms, I18n $i18n) {
        $locales = $i18n->getLocales();
        $node = $this->properties->getNode();
        $site = $node->getRootNode();

        if ($site->isLocalizationMethodCopy()) {
            $urls = $this->getUrlsForCopyTree($site, $node, $locales);
        } else {
            $urls = $this->getUrlsForUniqueTree($cms, $site, $node, $locales);
        }

        $this->setTemplateView($this->getTemplate(static::TEMPLATE_NAMESPACE . '/default'), array(
            'locales' => $urls,
        ));
    }

    /**
     * Gets the localized URL's for the current page in a localized copy site
     * @param \ride\library\cms\node\Node $site Instance of the site
     * @param \ride\library\cms\node\Node $node Instance of the node
     * @param array $locales All the locales
     * @return array Array with the localized URL's
     */
    private function getUrlsForCopyTree(Node $site, Node $node, array $locales) {
        $urls = array();

        // check for a content detail to localize detail pages
        $content = $this->getContext('content');
        if (isset($content->type)) {
            $contentMapper = $this->getContentFacade()->getContentMapper($content->type);
        } else {
            $content = null;
        }

        // gather the localized URL's for the provided node
        foreach ($locales as $localeCode => $locale) {
            if (!$node->isAvailableInLocale($localeCode)) {
                continue;
            }

            if ($content) {
                $urls[$localeCode] = array(
                    'url' => $contentMapper->getUrl($site->getId(), $localeCode, $content->data),
                    'locale' => $locale,
                );
            } else {
                $urls[$localeCode] = array(
                    'url' => $this->getUrl('cms.front.' . $site->getId() . '.' . $node->getId() . '.' . $localeCode),
                    'locale' => $locale,
                );
            }
        }

        // copy tree, put the localized URL's to the context
        $this->setContext('localizedUrls', $urls);

        return $urls;
    }

    /**
     * Gets the localized URL's for the home page in a localized unique site
     * @param \ride\library\cms\Cms $cms Instance of the CMS
     * @param \ride\library\cms\node\Node $site Instance of the site
     * @param \ride\library\cms\node\Node $node Instance of the node
     * @param array $locales All the locales
     * @return array Array with the localized URL's
     */
    private function getUrlsForUniqueTree(Cms $cms, Node $site, Node $node, array $locales) {
        $nodes = $cms->getNodeModel()->getNodes($site->getId(), $site->getRevision());

        $baseUrl = $this->request->getBaseScript();
        $urls = array();

        // gather the localized URL's for the home pages
        foreach ($locales as $localeCode => $locale) {
            if (!$site->isAvailableInLocale($localeCode)) {
                continue;
            }

            $url = null;
            $home = null;

            foreach ($nodes as $n) {
                if (!$n->isHomepage($localeCode)) {
                    continue;
                }

                $home = $n;

                break;
            }

            if ($home) {
                try {
                    $url = $this->getUrl('cms.front.' . $site->getId() . '.' . $home->getId() . '.' . $localeCode);
                } catch (RouterException $exception) {
                    $url = null;
                }
            }

            if (!$url) {
                $url = $site->getBaseUrl($localeCode);
                if (!$url) {
                    $url = $baseUrl;
                }
            }

            $urls[$localeCode] = array(
                'url' => $url,
                'locale' => $locale,
            );
        }

        if ($node->isHomePage($this->locale)) {
            // unique tree, no relations between the pages so we put the
            // localized URL's only on the context of the homepage
            $this->setContext('localizedUrls', $urls);
        }

        return $urls;
    }

    /**
     * Gets a preview of the properties of this widget instance
     * @return string
     */
    public function getPropertiesPreview() {
        $translator = $this->getTranslator();

        if ($this->getSecurityManager()->isPermissionGranted('cms.advanced')) {
            $template = $this->getTemplate(static::TEMPLATE_NAMESPACE . '/default');
        } else {
            $template = $this->getTemplateName($this->getTemplate(static::TEMPLATE_NAMESPACE . '/default'));
        }
        $preview = '<strong>' . $translator->translate('label.template') . '</strong>: ' . $template . '<br>';

        return $preview;
    }

    /**
     * Action to handle and show the properties of this widget
     * @return null
     */
    public function propertiesAction() {
        $translator = $this->getTranslator();

        $data = array(
            self::PROPERTY_TEMPLATE => $this->getTemplate(static::TEMPLATE_NAMESPACE . '/default'),
        );

        $form = $this->createFormBuilder($data);
        $form->addRow(self::PROPERTY_TEMPLATE, 'select', array(
            'label' => $translator->translate('label.template'),
            'description' => $translator->translate('label.template.widget.description'),
            'options' => $this->getAvailableTemplates(static::TEMPLATE_NAMESPACE),
            'validators' => array(
                'required' => array(),
            ),
        ));

        $form = $form->build();
        if ($form->isSubmitted()) {
            if ($this->request->getBodyParameter('cancel')) {
                return false;
            }

            try {
                $form->validate();

                $data = $form->getData();

                $this->setTemplate($data[self::PROPERTY_TEMPLATE]);

                return true;
            } catch (ValidationException $exception) {
                $this->setValidationException($exception, $form);
            }
        }

        $this->setTemplateView(static::TEMPLATE_NAMESPACE . '/properties', array(
            'form' => $form->getView(),
        ));

        return false;
    }

    /**
     * Gets the options for the styles
     * @return array Array with the name of the option as key and the
     * translation key as value
     */
    public function getWidgetStyleOptions() {
        return array(
            'container' => 'label.style.container',
            'menu' => 'label.style.menu',
        );
    }

    /**
     * Gets whether this widget caches when auto cache is enabled
     * @return boolean
     */
    public function isAutoCache() {
        return true;
    }

}
