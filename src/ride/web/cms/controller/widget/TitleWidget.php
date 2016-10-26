<?php

namespace ride\web\cms\controller\widget;

use ride\library\cms\node\PageNode;
use ride\library\cms\node\Node;
use ride\library\StringHelper;

use ride\web\cms\Cms;

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
     * Name of the anchor property
     * @var string
     */
    const PROPERTY_ANCHOR = 'anchor';

    /**
     * Name of the heading level property
     * @var string
     */
    const PROPERTY_HEADING = 'heading';

    /**
     * Namespace for the templates of this widget
     * @var string
     */
    const TEMPLATE_NAMESPACE = 'cms/widget/title';

    /**
     * Sets a title view to the response
     * @return null
     */
    public function indexAction() {
        $title = $this->properties->getLocalizedWidgetProperty($this->locale, self::PROPERTY_TITLE);
        $heading = $this->properties->getWidgetProperty(self::PROPERTY_HEADING);
        $anchor = $this->properties->getWidgetProperty(self::PROPERTY_ANCHOR);

        if ($title === null) {
            // title is being fetched from the context, force heading level to 1
            $heading = 1;
            $anchor = null;
        } elseif ($anchor) {
            // custom title, register in the context to create a TOC menu from titles
            $titles = $this->getContext('title.nodes', array());

            // make sure the anchor is unique
            $baseAnchor = StringHelper::safeString($title);
            $anchor = $baseAnchor;
            $index = 1;

            while (isset($titles[$anchor])) {
                $anchor = $baseAnchor . '-' . $index;
                $index++;
            }

            // create a node for the title
            $node = $this->properties->getNode();

            $titleNode = new PageNode();
            $titleNode->setParent($node);
            $titleNode->setName($this->locale, $title);
            $titleNode->setRoute($this->locale, $node->getRoute($this->locale) . '#' . $anchor);
            $titleNode->set(Node::PROPERTY_PUBLISH, true);
            $titleNode->set(self::PROPERTY_HEADING, $heading);

            $titles[$anchor] = $titleNode;

            $this->setContext('title.nodes', $titles);
        }

        $this->setTemplateView($this->getTemplate(static::TEMPLATE_NAMESPACE . '/default'), array(
            'title' => $title,
            'heading' => $heading,
            'anchor' => $anchor,
        ));
    }

    /**
     * Gets a preview of the properties of this widget instance
     * @return string
     */
    public function getPropertiesPreview() {
        $translator = $this->getTranslator();

        $title = $this->properties->getLocalizedWidgetProperty($this->locale, self::PROPERTY_TITLE);
        if ($title === null) {
            $title = $this->properties->getNode()->getName($this->locale, 'title');
        }

        $anchor = $this->properties->getWidgetProperty(self::PROPERTY_ANCHOR);
        if ($anchor) {
            $anchor = $translator->translate('label.yes');
        } else {
            $anchor = $translator->translate('label.no');
        }

        if ($this->getSecurityManager()->isPermissionGranted('cms.advanced')) {
            $template = $this->getTemplate(static::TEMPLATE_NAMESPACE . '/default');
        } else {
            $template = $this->getTemplateName($this->getTemplate(static::TEMPLATE_NAMESPACE . '/default'));
        }

        $preview = '';
        $preview .= '<strong>' . $translator->translate('label.title') . '</strong>: ' . $title . '<br>';
        $preview .= '<strong>' . $translator->translate('label.title.anchor') . '</strong>: ' . $anchor . '<br>';
        $preview .= '<strong>' . $translator->translate('label.template') . '</strong>: ' . $template . '<br>';

        return $preview;
    }

    /**
     * Action to handle and show the properties of this widget
     * @return null
     */
    public function propertiesAction() {
        $translator = $this->getTranslator();

        $title = $this->properties->getLocalizedWidgetProperty($this->locale, self::PROPERTY_TITLE);
        $heading = $this->properties->getWidgetProperty(self::PROPERTY_HEADING);
        $anchor = $this->properties->getWidgetProperty(self::PROPERTY_ANCHOR);
        $type = $title === null ? 'page' : 'custom';

        $data = array(
            'type' => $type,
            self::PROPERTY_TITLE => $title,
            self::PROPERTY_HEADING => $heading,
            self::PROPERTY_ANCHOR => $anchor,
            self::PROPERTY_TEMPLATE => $this->getTemplate(static::TEMPLATE_NAMESPACE . '/default'),
        );

        $form = $this->createFormBuilder($data);
        $form->addRow('type', 'option', array(
            'label' => $translator->translate('label.title.type'),
            'attributes' => array(
                'data-toggle-dependant' => 'option-type',
            ),
            'options' => array(
                'page' => $translator->translate('label.title.page'),
                'custom' => $translator->translate('label.title.custom'),
            ),
        ));
        $form->addRow(self::PROPERTY_TITLE, 'string', array(
            'label' => $translator->translate('label.title'),
            'description' => $translator->translate('label.title.description'),
            'attributes' => array(
                'class' => 'option-type option-type-custom',
            ),
            'filters' => array(
                'trim' => array(),
                'stripTags' => array(),
            ),
            'localized' => true,
        ));
        $form->addRow(self::PROPERTY_HEADING, 'option', array(
            'label' => $translator->translate('label.heading'),
            'description' => $translator->translate('label.title.heading.description'),
            'attributes' => array(
                'class' => 'option-type option-type-custom',
            ),
            'options' => array(
                2 => 2,
                3 => 3,
                4 => 4,
                5 => 5,
            ),
            'widget' => 'select'
        ));
        $form->addRow(self::PROPERTY_ANCHOR, 'option', array(
            'label' => $translator->translate('label.title.anchor'),
            'description' => $translator->translate('label.title.anchor.description'),
            'attributes' => array(
                'class' => 'option-type option-type-custom',
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

        $form = $form->build();
        if ($form->isSubmitted()) {
            if ($this->request->getBodyParameter('cancel')) {
                return false;
            }

            try {
                $form->validate();

                $data = $form->getData();

                if ($data['type'] == 'page') {
                    $data[self::PROPERTY_TITLE] = null;
                    $data[self::PROPERTY_HEADING] = null;
                    $data[self::PROPERTY_ANCHOR] = null;
                }

                $this->properties->setLocalizedWidgetProperty($this->locale, self::PROPERTY_TITLE, $data[self::PROPERTY_TITLE]);
                $this->properties->setWidgetProperty(self::PROPERTY_HEADING, $data[self::PROPERTY_HEADING]);
                $this->properties->setWidgetProperty(self::PROPERTY_ANCHOR, $data[self::PROPERTY_ANCHOR] ? "1" : null);
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
            'title' => 'label.style.title',
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
