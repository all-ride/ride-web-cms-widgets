<?php

namespace ride\web\cms\controller\widget;

use ride\library\validation\exception\ValidationException;

use ride\web\cms\form\SocialMediaComponent;

use ride\library\config\Config;

/**
 * Widget which links to chosen social media profiles
 */
class SocialMediaLinksWidget extends AbstractWidget implements StyleWidget{

    /**
     * Machine name of this widget
     * @var string
     */
    const NAME = 'social.media.links';

    /**
     * Path to the icon of this widget
     * @var string
     */
    const ICON = 'img/cms/widget/social.png';

    /**
     * Namespace for the templates of this widget
     * @var string
     */
    const TEMPLATE_NAMESPACE = 'cms/widget/social-media';

    /**
     * Name of title property
     * @var string
     */
    const PROPERTY_TITLE = 'title';

    /**
     * Render the social media links.
     * @param Config $config
     */
    public function indexAction(Config $config) {
        $socialMedia = $config->get('social');
        $values =  unserialize($this->properties->getWidgetProperty('social.widgets'));
        if (!$values) {
            return;
        }

        $data = array(
            'socialMedia' => array(),
            'title' => $this->properties->getLocalizedWidgetProperty($this->locale, self::PROPERTY_TITLE),
        );

        foreach ($values as $value) {
            if (isset($value['socialMediaName']) && isset($socialMedia[$value['socialMediaName']])) {
                $data['socialMedia'][] = array(
                    'name' => $value['socialMediaName'],
                    'url' => $socialMedia[$value['socialMediaName']]['profileUrl'] .
                        $value['accountName'],
                );
            }
        }
        $this->setTemplateView($this->getTemplate(static::TEMPLATE_NAMESPACE . '/social.media.links'), $data);
    }

    /**
     * Gets a preview of the properties of this widget instance
     * @return string
     */
    public function getPropertiesPreview() {
        $preview = "";
        if ($this->properties->getLocalizedWidgetProperty($this->locale, self::PROPERTY_TITLE)) {
            $preview .= "<strong>" . $this->getTranslator()->translate('label.title') . "</strong>: " .
                $this->properties->getLocalizedWidgetProperty($this->locale, self::PROPERTY_TITLE) . "<br />";
        }
        $values = unserialize($this->properties->getWidgetProperty('social.widgets'));
        if ($values) {
            $links = array();
            foreach ($values as $value) {
                $links[] = $value['socialMediaName'] . ' (' . $value['accountName'] . ')';
            }
            $preview .= '<strong>' . $this->getTranslator()->translate('label.social.media') . '</strong>: ' . implode(', ', $links);
        }

        return $preview;
    }

    /**
     * Action to handle and show the properties of this widget
     * @param SocialMediaComponent $component
     * @return null
     */
    public function propertiesAction(SocialMediaComponent $component) {
        $translator = $this->getTranslator();

        $unserializedData = unserialize($this->properties->getWidgetProperty('social.widgets'));
        if (!$unserializedData) {

            $unserializedData = array();
        }
        $data = array(
            'title' => $this->properties->getLocalizedWidgetProperty($this->locale, self::PROPERTY_TITLE),
            'socialMedia' => $unserializedData,
        );

        $form = $this->createFormBuilder($data);
        $form->addRow('title', 'string', array(
           'label' => $translator->translate('label.title'),
        ));
        $form->addRow('socialMedia', 'collection', array(
            'label' => $translator->translate('label.social.media'),
            'type' => 'component',
            'options' => array(
                'component' => $component,
            ),
            'embed' => true,
            'multiple' => true,
            "order" => true,
        ));

        $form = $form->build();
        if ($form->isSubmitted()) {
            if ($this->request->getBodyParameter('cancel')) {
                return false;
            }

            try {
                $form->validate();
                $data = $form->getData();

                $values = array();
                foreach ($data['socialMedia'] as $media) {
                    $values[] = array(
                        'socialMediaName' => $media['socialMediaName'],
                        'accountName' => $media['accountName'],
                    );
                }

                if ($values) {
                    $this->properties->setWidgetProperty('social.widgets', serialize($values));
                }
                $this->properties->setLocalizedWidgetProperty($this->locale, self::PROPERTY_TITLE, $data['title'] ? $data['title'] : '');


                return true;
            } catch (ValidationException $exception) {
                $this->setValidationException($exception, $form);
            }
        }

        $view = $this->setTemplateView(self::TEMPLATE_NAMESPACE . '/properties', array(
            'form' => $form->getView(),
        ));

        $view->addJavascript('js/form.js');

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
            'title'     => 'label.style.title',
        );
    }

}
