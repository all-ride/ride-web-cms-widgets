<?php

namespace ride\web\cms\controller\widget;

use ride\library\validation\exception\ValidationException;

/**
 * Widget which shares the current page to the chosen social media
 */
class SocialMediaShareWidget extends AbstractWidget implements StyleWidget{

    /**
     * Machine name of this widget
     * @var string
     */
    const NAME = 'social.media.share';

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
     * Name of the share media property
     * @var string
     */
    const PROPERTY_SHARE_MEDIA = 'share.media';

    /**
     * Name of title property
     * @var string
     */
    const PROPERTY_TITLE = 'title';

    /**
     * Render the social media links.
     */
    public function indexAction() {
        $media = $this->properties->getWidgetProperty(self::PROPERTY_SHARE_MEDIA);
        $data = array(
            'socialMedia' => $media ? explode(',', $media) : array(),
            'title' => $this->properties->getLocalizedWidgetProperty($this->locale, self::PROPERTY_TITLE),
        );

        $this->setTemplateView($this->getTemplate(static::TEMPLATE_NAMESPACE . '/social.media.share'), $data);
    }

    /**
     * Gets a preview of the properties of this widget instance
     * @return string
     */
    public function getPropertiesPreview() {
        $preview = "";
        if ($this->properties->getLocalizedWidgetProperty($this->locale, self::PROPERTY_TITLE)) {
            $preview .= '<strong>' . $this->getTranslator()->translate('label.title') . '</strong>: ' . $this->properties->getLocalizedWidgetProperty($this->locale, self::PROPERTY_TITLE) . '<br />';
        }

        $data = $this->properties->getLocalizedWidgetProperty($this->locale, self::PROPERTY_SHARE_MEDIA);
        if ($data) {
            $data = explode(',', $data);
            $preview .= '<strong>' . $this->getTranslator()->translate('label.social.media.services.enabled') . '</strong>: ' . implode(', ', $data);
        }

        return $preview;
    }

    /**
     * Action to handle and show the properties of this widget
     * @return null
     */
    public function propertiesAction() {
        $translator = $this->getTranslator();

        $data = array(
            'title' => $this->properties->getLocalizedWidgetProperty($this->locale, self::PROPERTY_TITLE),
            'shareMedia' => explode(',', $this->properties->getWidgetProperty(self::PROPERTY_SHARE_MEDIA)),
        );

        $form = $this->createFormBuilder($data);
        $form->addRow('title', 'string', array(
            'label' => $translator->translate('label.title'),
        ));
        $form->addRow('shareMedia', 'option', array(
            'label' => $translator->translate('label.social.media'),
            'options' => array(
                'email' => 'Email',
                'digg' => 'Digg',
                'facebook' => 'Facebook',
                'google' => 'GooglePlus',
                'linkedin' => 'LinkedIn',
                'pinterest' => 'Pintrest',
                'reddit' => 'Reddit',
                'stumbleUpon' => 'StumbleUpon',
                'tumblr' => 'Tumblr',
                'twitter' => 'Twitter',
            ),
            'multiple' => true,
            'widget' => 'select',
            'order' => true,
            'validators' => array(
                'required' => array()
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
                if ($data['shareMedia']) {
                    $values = implode(',', array_keys($data['shareMedia']));
                    $this->properties->setWidgetProperty(self::PROPERTY_SHARE_MEDIA, $values);
                }
                $this->properties->setLocalizedWidgetProperty($this->locale, self::PROPERTY_TITLE, $data['title']);

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
            'title' => 'label.style.title',
        );
    }

}
