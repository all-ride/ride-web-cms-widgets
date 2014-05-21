<?php

namespace ride\web\cms\controller\widget;

use ride\library\i18n\I18n;
use ride\library\validation\exception\ValidationException;
use ride\library\StringHelper;

use \ride\web\cms\text\Text;

/**
 * Widget to show a static text block
 */
class TextWidget extends AbstractWidget implements StyleWidget {

	/**
	 * Machine name of this widget
	 * @var string
	 */
    const NAME = 'text';

    /**
     * Path to the icon of this widget
     * @var string
     */
    const ICON = 'img/cms/widget/text.png';

    /**
     * Path to the template of the widget view
     * @var string
     */
    const TEMPLATE = 'cms/widget/text/text';

    /**
     * Parameter for the default format
     * @var string
     */
    const PARAM_DEFAULT_FORMAT = 'cms.text.format';

    /**
     * Parameter for the default IO
     * @var string
     */
    const PARAM_DEFAULT_IO = 'cms.text.io';

    /**
     * Name of the text property
     * @var string
     */
    const PROPERTY_TEXT = 'text';

    /**
     * Name of the format property
     * @var string
     */
    const PROPERTY_FORMAT = 'format';

    /**
     * Name of the title property
     * @var string
     */
    const PROPERTY_TITLE = 'title';

    /**
     * Name of the body property
     * @var string
     */
    const PROPERTY_BODY = 'body';

    /**
     * Name of the image property
     * @var string
     */
    const PROPERTY_IMAGE = 'image-src';

    /**
     * Name of the image alignment property
     * @var string
     */
    const PROPERTY_IMAGE_ALIGNMENT = 'image-align';

    /**
     * Name of the I/O property
     * @var string
     */
    const PROPERTY_IO = 'io';

    /**
     * Sets a text view to the response
     * @return null
     */
    public function indexAction() {
        $text = $this->getTextIO()->getText($this->properties, $this->locale);
        $textFormat = $this->getTextFormat($text->getFormat());

        $html = $textFormat->getHtml($text->getBody());

        $this->setTemplateView(self::TEMPLATE, array(
            'text' => $text,
            'title' => $text->getTitle(),
            'html' => $html,
            'image' => $text->getImage(),
            'imageAlignment' => $text->getImageAlignment(),
        ));

        if ($this->properties->isAutoCache()) {
            $this->properties->setCache(true);
        }
    }

    /**
     * Gets a preview for the properties of this widget
     * @return string
     */
    public function getPropertiesPreview() {
        $text = $this->getTextIO()->getText($this->properties, $this->locale);
        $textFormat = $this->getTextFormat($text->getFormat());

        $title = $text->getTitle();
        $body = $textFormat->getHtml($text->getBody());
        $image = $text->getImage();

        $bodyStripped = trim(strip_tags($body));
        if (!$bodyStripped) {
            $body = htmlentities($body);
        } else {
            $body = StringHelper::truncate($bodyStripped, 120);
        }

        $preview = '';

        if ($title) {
            $preview .= '<strong>' . $title . '</strong><br>';
        }

        $preview .= $body;

        return $preview;
    }

    /**
     * Action to handle and show the properties of this widget
     * @param \ride\library\i18n\I18n $i18n
     * @return null
     */
    public function propertiesAction(I18n $i18n) {
        $locales = $i18n->getLocaleCodeList();
        $hasMultipleLocales = count($locales) != 1;

        // get the data
        $io = $this->getTextIO();

        $text = $io->getText($this->properties, $this->locale);
        if (!$text->getFormat()) {
            $text->setFormat($this->getDefaultTextFormat());
        }

        $format = $this->getTextFormat($text->getFormat());

        $data = array(
            self::PROPERTY_TITLE => $text->getTitle(),
            self::PROPERTY_BODY => $text->getBody(),
            self::PROPERTY_IMAGE => $text->getImage(),
            self::PROPERTY_IMAGE_ALIGNMENT => $text->getImageAlignment(),
        );

        // create the form
        $translator = $this->getTranslator();

        $form = $this->createFormBuilder($data);
        $form->setId('form-text');
        $form->addRow(self::PROPERTY_TITLE, 'string', array(
            'label' => $translator->translate('label.title'),
            'filters' => array(
                'trim' => array(),
            ),
        ));

        $format->processForm($form, $translator, $this->locale);
        $io->processForm($this->properties, $this->locale, $translator, $text, $form);

        $form->addRow(self::PROPERTY_IMAGE, 'image', array(
            'label' => $translator->translate('label.image'),
        ));
        $form->addRow(self::PROPERTY_IMAGE_ALIGNMENT, 'select', array(
            'label' => $translator->translate('label.alignment.image'),
            'options' => array(
                Text::ALIGN_LEFT => $translator->translate('align.left'),
                Text::ALIGN_RIGHT => $translator->translate('align.right'),
            ),
        ));

        if ($hasMultipleLocales) {
            $form->addRow('locales-all', 'option', array(
                'label' => '',
                'description' => $translator->translate('label.text.locales.all'),
            ));
        }

        // handle form submission
        $form = $form->build();
        if ($form->isSubmitted()) {
            try {
                $form->validate();

                $data = $form->getData();

                $this->properties->setWidgetProperty(self::PROPERTY_IO, $io->getName());

                $format->setText($text, $data);

                if ($hasMultipleLocales && $data['locales-all']) {
                    $io->setText($this->properties, $locales, $text, $data);
                } else {
                    $io->setText($this->properties, $this->locale, $text, $data);
                }

                return true;
            } catch (ValidationException $exception) {
                $this->setValidationException($exception, $form);
            }
        }

        // set view
        $view = $this->setTemplateView('cms/widget/text/properties', array(
            'form' => $form->getView(),
            'io' => $io,
            'format' => $format,
            'text' => $text,
        ));
        $view->addJavascript('js/cms/text.js');

        $form->processView($view);

        return false;
    }

    /**
     * Gets the instance of the text format
     * @param string $format Machine name of the text format
     * @return \ride\web\cms\text\format\TextFormat
     */
    protected function getTextFormat($format = null) {
        return $this->dependencyInjector->get('ride\\web\\cms\\text\\format\\TextFormat', $format);
    }

    /**
     * Gets the name of the default text format
     * @return string Machine name of the text format
     */
    protected function getDefaultTextFormat() {
        return $this->config->get(self::PARAM_DEFAULT_FORMAT, 'plain');
    }

    /**
     * Gets the text IO
     * @return \ride\web\cms\text\io\TextIO;
     */
    protected function getTextIO() {
        $io = $this->getDefaultTextIO();
        $io = $this->properties->getWidgetProperty(self::PROPERTY_IO, $io);

        if (!$io) {
            $io = null;
        }

        return $this->dependencyInjector->get('ride\\web\\cms\\text\\io\\TextIO', $io);
    }

    /**
     * Gets the name of the default text IO
     * @return string Machine name of the text IO
     */
    protected function getDefaultTextIO() {
        return $this->config->get(self::PARAM_DEFAULT_IO, 'properties');
    }

    /**
     * Gets the text to display
     * @return string
     */
    protected function getText() {
        $text = $this->getTextIO()->getText($this->properties, $this->locale);
        $textFormat = $this->getTextFormat($text->getFormat());

        return $textFormat->getHtml($text->getBody());
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
