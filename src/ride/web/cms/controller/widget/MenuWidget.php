<?php

namespace ride\web\cms\controller\widget;

use ride\library\cms\exception\NodeNotFoundException;
use ride\library\cms\node\Node;

use ride\web\cms\Cms;

/**
 * Widget to show a menu of the node tree or a part thereof
 */
class MenuWidget extends AbstractWidget implements StyleWidget {

    /**
     * Machine name of this widget
     * @var string
     */
    const NAME = 'menu';

    /**
     * Path to the icon of this widget
     * @var string
     */
    const ICON = 'img/cms/widget/menu.png';

    /**
     * Namespace for the templates of this widget
     * @var string
     */
    const TEMPLATE_NAMESPACE = 'cms/widget/menu';

    /**
     * Default depth value of a menu widget
     * @var int
     */
    const DEFAULT_DEPTH = 1;

    /**
     * Default parent value of a menu widget
     * @var string
     */
    const DEFAULT_PARENT = 'default';

    /**
     * Default show title value of a menu widget
     * @var boolean
     */
    const DEFAULT_SHOW_TITLE = false;

    /**
     * Parent prefix for a absolute parent
     * @var string
     */
    const PARENT_ABSOLUTE = 'absolute';

    /**
     * Parent value for the current node
     * @var string
     */
    const PARENT_CURRENT = 'current';

    /**
     * Parent prefix for a relative parent
     * @var string
     */
    const PARENT_RELATIVE = 'relative';

    /**
     * Setting key for the parent value
     * @var string
     */
    const PROPERTY_PARENT = 'node';

    /**
     * Setting key for the depth value
     * @var string
     */
    const PROPERTY_DEPTH = 'depth';

    /**
     * Setting key for the title value
     * @var string
     */
    const PROPERTY_SHOW_TITLE = 'title';

    /**
     * Facade to the CMS
     * @var \ride\web\cms\Cms
     */
    protected $cms;

    /**
     * Constructs a new menu widget
     * @param \ride\web\cms\Cms $cms Facade of the CMS
     * @return null;
     */
    public function __construct(Cms $cms) {
        $this->cms = $cms;
    }

    /**
     * Sets a title view to the response
     * @return null
     */
    public function indexAction() {
        $parent = $this->getParent();
        $depth = $this->getDepth();
        $showTitle = $this->getShowTitle();

        if (!$parent) {
            return;
        }

        $node = $this->properties->getNode();
        try {
            $parentNode = $this->cms->getNode($node->getRootNodeId(), $node->getRevision(), $parent, null, true, $depth);
        } catch (NodeNotFoundException $exception) {
            $this->getLog()->logException($exception);

            return;
        }

        $nodes = $parentNode->getChildren();

        $title = null;
        if ($showTitle) {
            $title = $parentNode->getName($this->locale);
        }

        $this->setTemplateView($this->getTemplate(static::TEMPLATE_NAMESPACE . '/default'), array(
            'title' => $title,
            'depth' => $depth,
            'nodeTypes' => $this->cms->getNodeTypes(),
            'items' => $nodes,
        ));
    }

    /**
     * Get a preview of the properties of this widget
     * @return string
     */
    public function getPropertiesPreview() {
        $translator = $this->getTranslator();

        $parent = $this->getParent();
        $depth = $this->getDepth();
        $showTitle = $this->getShowTitle();

        if ($parent) {
            $node = $this->properties->getNode();

            try {
                $parentNode = $this->cms->getNode($node->getRootNodeId(), $node->getRevision(), $parent);
                $parent = $parentNode->getName($this->locale);
            } catch (NodeNotFoundException $exception) {
                $parent = 'unexistant';
            }
        } else {
            $parent = '---';
        }

        $preview = '';
        $preview .= '<strong>' . $translator->translate('label.menu.parent') . '</strong>: ' . $parent . '<br>';
        $preview .= '<strong>' . $translator->translate('label.menu.depth') . '</strong>: ' . $depth . '<br>';
        $preview .= '<strong>' . $translator->translate('label.title.show') . '</strong>: ' . $translator->translate($showTitle ? 'label.yes' : 'label.no') . '<br>';
        $preview .= '<strong>' . $translator->translate('label.template') . '</strong>: ' . $this->getTemplate(static::TEMPLATE_NAMESPACE . '/default') . '<br>';

        return $preview;
    }

    /**
     * Action to handle and show the properties of this widget
     * @return null
     */
    public function propertiesAction() {
        $translator = $this->getTranslator();

        $node = $this->properties->getNode();
        $rootNodeId = $node->getRootNodeId();

        $site = $this->cms->getNode($rootNodeId, $node->getRevision(), $rootNodeId, null, true);
        $levels = $this->cms->getChildrenLevels($site) - 1;

        $nodeList = $this->cms->getNodeList($site, $this->locale, true, true, false);
        $nodeList[self::PARENT_CURRENT] = $translator->translate('label.menu.parent.current');
        for ($i = 1; $i <= $levels; $i++) {
            $nodeList[self::PARENT_ABSOLUTE . $i] = $translator->translate('label.menu.parent.absolute', array('level' => $i));
        }
        for ($i = 0; $i < $levels; $i++) {
            $level = $i + 1;
            $nodeList[self::PARENT_RELATIVE . $level] = $translator->translate('label.menu.parent.relative', array('level' => '-' . $level));
        }

        $depths = array();
        for ($i = 1, $j = $levels + 1; $i <= $j; $i++) {
            $depths[$i] = $i;
        }

        $data = array(
            self::PROPERTY_PARENT => $this->getParent(false),
            self::PROPERTY_DEPTH => $this->getDepth(),
            self::PROPERTY_SHOW_TITLE => $this->getShowTitle(),
            self::PROPERTY_TEMPLATE => $this->getTemplate(static::TEMPLATE_NAMESPACE . '/default'),
        );

        $form = $this->createFormBuilder($data);
        $form->addRow(self::PROPERTY_PARENT, 'select', array(
            'label' => $translator->translate('label.menu.parent'),
            'description' => $translator->translate('label.menu.parent.description'),
            'options' => $nodeList,
            'validators' => array(
                'required' => array(),
            )
        ));
        $form->addRow(self::PROPERTY_DEPTH, 'select', array(
            'label' => $translator->translate('label.menu.depth'),
            'description' => $translator->translate('label.menu.depth.description'),
            'options' => $depths,
        ));
        $form->addRow(self::PROPERTY_SHOW_TITLE, 'option', array(
            'label' => $translator->translate('label.title.show'),
            'description' => $translator->translate('label.menu.title.show.description'),
        ));
        $form->addRow(self::PROPERTY_TEMPLATE, 'select', array(
            'label' => $translator->translate('label.template'),
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

                $this->properties->setWidgetProperty(self::PROPERTY_PARENT, $data[self::PROPERTY_PARENT]);
                $this->properties->setWidgetProperty(self::PROPERTY_DEPTH, $data[self::PROPERTY_DEPTH]);
                $this->properties->setWidgetProperty(self::PROPERTY_SHOW_TITLE, $data[self::PROPERTY_SHOW_TITLE]);

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
     * Get the value for the parent node
     * @param boolean $fetchNodeId Set to false to skip the lookup of the node id
     * @return string
     */
    private function getParent($fetchNodeId = true) {
        $parent = $this->properties->getWidgetProperty(self::PROPERTY_PARENT, self::DEFAULT_PARENT);

        if (!$fetchNodeId) {
            return $parent;
        }

        if ($parent === self::DEFAULT_PARENT) {
            return $this->properties->getNode()->getRootNodeId();
        }

        if ($parent === self::PARENT_CURRENT) {
            return $this->properties->getNode()->getId();
        }

        $path = $this->properties->getNode()->getPath();
        $tokens = explode(Node::PATH_SEPARATOR, $path);

        if (strpos($parent, self::PARENT_ABSOLUTE) !== false) {
            $level = str_replace(self::PARENT_ABSOLUTE, '', $parent);
        } elseif (strpos($parent, self::PARENT_RELATIVE) !== false) {
            $level = str_replace(self::PARENT_RELATIVE, '', $parent);
            $tokens = array_reverse($tokens);
        } else {
            return $parent;
        }

        if (!isset($tokens[$level])) {
            // not existant
            return null;
        }

        return $tokens[$level];
    }

    /**
     * Get the depth value
     * @return integer
     */
    private function getDepth() {
        return $this->properties->getWidgetProperty(self::PROPERTY_DEPTH, self::DEFAULT_DEPTH);
    }

    /**
     * Get the show title value
     * @return boolean
     */
    private function getShowTitle() {
        return $this->properties->getWidgetProperty(self::PROPERTY_SHOW_TITLE, self::DEFAULT_SHOW_TITLE);
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
