<?php

namespace ride\web\cms\controller\widget;

use ride\library\cms\exception\NodeNotFoundException;
use ride\library\cms\node\NodeModel;
use ride\library\i18n\I18n;
use ride\library\validation\exception\ValidationException;
use ride\library\StringHelper;

use \ride\web\cms\form\CallToActionComponent;
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
     * Name of the subtitle property
     * @var string
     */
    const PROPERTY_SUBTITLE = 'subtitle';

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
     * Name of the CTA property
     * @var string
     */
    const PROPERTY_CTA = 'cta';

    /**
     * Name of the I/O property
     * @var string
     */
    const PROPERTY_IO = 'io';

    /**
     * Namespace for the templates of this widget
     * @var string
     */
    const TEMPLATE_NAMESPACE = 'cms/widget/text';

    /**
     * Default template of this widget
     * @var string
     */
    const TEMPLATE_DEFAULT = 'default';

    /**
     * Sets a text view to the response
     * @return null
     */
    public function indexAction(NodeModel $nodeModel) {
        $propertiesNode = $this->properties->getNode();
        $rootNodeId = $propertiesNode->getRootNodeId();

        $text = $this->getTextIO()->getText($this->properties, $this->locale);
        $textFormat = $this->getTextFormat($text->getFormat());

        $html = $textFormat->getHtml($text->getBody());
        $callToActions = $text->getCallToActions();
        foreach ($callToActions as $index => $callToAction) {
            $node = $callToAction->getNode();
            $url = $callToAction->getUrl();
            $suffix = $callToAction->getSuffix();

            if ($node) {
                try {
                    $node = $nodeModel->getNode($propertiesNode->getRootNodeId(), $propertiesNode->getRevision(), $node);

                    $callToAction->setUrl($this->getUrl('cms.front.' . $rootNodeId . '.' . $node->getId() . '.' . $this->locale) . $callToAction->getSuffix());
                } catch (NodeNotFoundException $exception) {
                    $this->getLog()->logException($exception);

                    unset($callToActions[$index]);
                }
            } elseif ($url) {
                $callToAction->setUrl($this->properties->getNode()->resolveUrl($this->locale, $this->request->getBaseUrl(), $url));
            } elseif (!$suffix) {
                unset($callToActions[$index]);
            }
        }

        $this->setTemplateView($this->getTemplate(static::TEMPLATE_NAMESPACE . '/' . static::TEMPLATE_DEFAULT), array(
            'text' => $text,
            'title' => $text->getTitle(),
            'subtitle' => $text->getSubtitle(),
            'html' => $html,
            'image' => $text->getImage(),
            'imageAlignment' => $text->getImageAlignment(),
            'callToActions' => $callToActions,
        ));
    }

    /**
     * Gets a preview for the properties of this widget
     * @return string
     */
    public function getPropertiesPreview() {
        $text = $this->getTextIO()->getText($this->properties, $this->locale);
        $textFormat = $this->getTextFormat($text->getFormat());

        $title = $text->getTitle();
        $subtitle = $text->getSubtitle();
        $body = $textFormat->getHtml($text->getBody());
        $image = $text->getImage();

        $bodyStripped = trim(strip_tags($body));
        if (!$bodyStripped) {
            $body = htmlentities($body);
        } else {
            $body = StringHelper::truncate($bodyStripped, 120);
        }

        $translator = $this->getTranslator();
        $preview = '';

        if ($title) {
            $preview .= '<strong>' . $translator->translate('label.title') . '</strong>: ' . $title . '<br>';
        }
        if ($subtitle) {
            $preview .= '<strong>' . $translator->translate('label.subtitle') . '</strong>: ' . $subtitle . '<br>';
        }
        $preview .= '<strong>' . $translator->translate('label.template') . '</strong>: ' . $this->getTemplate(static::TEMPLATE_NAMESPACE . '/' . static::TEMPLATE_DEFAULT) . '<br>';

        $preview .= $body;

        return $preview;
    }

    /**
     * Action to handle and show the properties of this widget
     * @param \ride\library\i18n\I18n $i18n
     * @return null
     */
    public function propertiesAction(I18n $i18n, NodeModel $nodeModel) {
        $locales = $i18n->getLocaleCodeList();
        $hasMultipleLocales = count($locales) != 1;

        // get the data
        $io = $this->getTextIO();

        $existing = $this->request->getQueryParameter('text');
        if ($existing) {
            $text = $io->getExistingText($this->properties, $this->locale, $existing, $this->request->getQueryParameter('new'));
        } else {
            $text = $io->getText($this->properties, $this->locale);
        }

        if (!$text->getFormat()) {
            $text->setFormat($this->getDefaultTextFormat());
        }

        $format = $this->getTextFormat($text->getFormat());

        $data = array(
            self::PROPERTY_TITLE => $text->getTitle(),
            self::PROPERTY_SUBTITLE => $text->getSubtitle(),
            self::PROPERTY_BODY => $text->getBody(),
            self::PROPERTY_IMAGE => $text->getImage(),
            self::PROPERTY_IMAGE_ALIGNMENT => $text->getImageAlignment(),
            self::PROPERTY_CTA => $text->getCallToActions(),
            self::PROPERTY_TEMPLATE => $this->getTemplate(static::TEMPLATE_NAMESPACE . '/' . static::TEMPLATE_DEFAULT),
        );
        $data['title-use'] = $data[self::PROPERTY_TITLE] || $data[self::PROPERTY_SUBTITLE];
        $data['image-use'] = $data[self::PROPERTY_IMAGE];

        $io->processFormData($text, $data);

        // create the form
        $translator = $this->getTranslator();

        $ctaComponent = new CallToActionComponent();
        $ctaComponent->setNodes($this->getNodeList($nodeModel));

        $types = $this->config->get('cms.text.cta.type');
        if (is_array($types)) {
            $types = $this->config->getConfigHelper()->flattenConfig($types);
            foreach ($types as $index => $label) {
                $types[$index] = $translator->translate($label);
            }

            $ctaComponent->setTypes(array('' => '---') + $types);
        }

        $form = $this->createFormBuilder($data);
        $form->setId('form-text');

        $format->processForm($form, $translator, $this->locale);
        $io->processForm($this->properties, $this->locale, $translator, $text, $form);

        $form->addRow('title-use', 'option', array(
            'label' => ' ',
            'description' => $translator->translate('label.title.use'),
            'attributes' => array(
                'data-toggle-dependant' => 'option-title',
            ),
        ));
        $form->addRow(self::PROPERTY_TITLE, 'string', array(
            'label' => $translator->translate('label.title'),
            'attributes' => array(
                'class' => 'option-title option-title-1',
            ),
            'filters' => array(
                'trim' => array(),
            ),
        ));
        $form->addRow(self::PROPERTY_SUBTITLE, 'string', array(
            'label' => $translator->translate('label.subtitle'),
            'attributes' => array(
                'class' => 'option-title option-title-1',
            ),
            'filters' => array(
                'trim' => array(),
            ),
        ));
        $form->addRow('image-use', 'option', array(
            'label' => ' ',
            'description' => $translator->translate('label.image.use'),
            'attributes' => array(
                'data-toggle-dependant' => 'option-image',
            ),
        ));
        $form->addRow(self::PROPERTY_IMAGE, 'image', array(
            'label' => $translator->translate('label.image'),
            'attributes' => array(
                'class' => 'option-image option-image-1',
            ),
        ));
        $form->addRow(self::PROPERTY_IMAGE_ALIGNMENT, 'select', array(
            'label' => $translator->translate('label.alignment.image'),
            'attributes' => array(
                'class' => 'option-image option-image-1',
            ),
            'options' => array(
                Text::ALIGN_NONE => $translator->translate('align.none'),
                Text::ALIGN_LEFT => $translator->translate('align.left'),
                Text::ALIGN_RIGHT => $translator->translate('align.right'),
                Text::ALIGN_JUSTIFY => $translator->translate('align.justify'),
            ),
        ));
        $form->addRow(self::PROPERTY_CTA, 'collection', array(
            'label' => $translator->translate('label.cta'),
            'type' => 'component',
            'options' => array(
                'component' => $ctaComponent,
            ),
        ));
        $form->addRow(self::PROPERTY_TEMPLATE, 'select', array(
            'label' => $translator->translate('label.template'),
            'description' => $translator->translate('label.template.widget.description'),
            'options' => $this->getAvailableTemplates(static::TEMPLATE_NAMESPACE),
            'validators' => array(
                'required' => array(),
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
            if ($existing) {
                $form->setData($data);
            } else {
                try {
                    $form->validate();

                    $data = $form->getData();
                    if (!$data['title-use']) {
                        $data[self::PROPERTY_TITLE] = '';
                        $data[self::PROPERTY_SUBTITLE] = '';
                    }
                    if (!$data['image-use']) {
                        $data[self::PROPERTY_IMAGE] = '';
                    }

                    $this->properties->setWidgetProperty(self::PROPERTY_IO, $io->getName());

                    $format->setText($text, $data);

                    if ($hasMultipleLocales && $data['locales-all']) {
                        $io->setText($this->properties, $locales, $text, $data);
                    } else {
                        $io->setText($this->properties, $this->locale, $text, $data);
                    }

                    $this->setTemplate($data[self::PROPERTY_TEMPLATE]);

                    return true;
                } catch (ValidationException $exception) {
                    $this->setValidationException($exception, $form);
                }
            }
        }

        // set view
        $node = $this->properties->getNode();
        $action = $this->getUrl('cms.node.content.widget.properties', array(
            'locale' => $this->locale,
            'site' => $node->getRootNodeId(),
        	'revision' => $node->getRevision(),
        	'node' => $node->getId(),
        	'region' => $this->region,
        	'section' => $this->section,
        	'block' => $this->block,
            'widget' => $this->id,
        ));

        $view = $this->setTemplateView(static::TEMPLATE_NAMESPACE . '/properties', array(
            'form' => $form->getView(),
            'io' => $io,
            'format' => $format,
            'text' => $text,
            'action' => $action,
        ));
        $view->addJavascript('js/cms/text.js');

        $io->processFormView($this->properties, $translator, $text, $view);
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
            'container' => 'label.style.container',
            'title' => 'label.style.title',
            'subtitle' => 'label.style.subtitle',
            'cta' => 'label.style.cta',
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
