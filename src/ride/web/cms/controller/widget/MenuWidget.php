<?php

namespace ride\web\cms\controller\widget;

use ride\library\cms\exception\NodeNotFoundException;
use ride\library\cms\node\Node;
use ride\library\validation\constraint\ConditionalConstraint;
use ride\library\validation\exception\ValidationException;
use ride\library\validation\factory\ValidationFactory;

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
     * Setting key for the nodes value
     * @var string
     */
    const PROPERTY_NODES = 'nodes';

    /**
     * Setting key for the depth value
     * @var string
     */
    const PROPERTY_DEPTH = 'depth';

    /**
     * Setting key for the title value
     * @var string
     */
    const PROPERTY_TITLE = 'title';

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

        $node = $this->properties->getNode();
        if ($parent) {
            try {
                $parentNode = $this->cms->getNode($node->getRootNodeId(), $node->getRevision(), $parent, null, true, $depth);
            } catch (NodeNotFoundException $exception) {
                $this->getLog()->logException($exception);

                return;
            }

            $items = $parentNode->getChildren();
        } else {
            $parentNode = null;
            $items = array();

            $nodeIds = $this->getNodeIds();
            foreach ($nodeIds as $nodeId) {
                try {
                    $items[$nodeId] = $this->cms->getNode($node->getRootNodeId(), $node->getRevision(), $nodeId, null, true, $depth);
                } catch (NodeNotFoundException $exception) {
                    $this->getLog()->logException($exception);

                    return;
                }
            }
        }

        $this->setTemplateView($this->getTemplate(static::TEMPLATE_NAMESPACE . '/default'), array(
            'title' => $this->getTitle($parentNode),
            'depth' => $depth,
            'nodeTypes' => $this->cms->getNodeTypes(),
            'parent' => $parentNode,
            'items' => $items,
        ));
    }

    /**
     * Get a preview of the properties of this widget
     * @return string
     */
    public function getPropertiesPreview() {
        $translator = $this->getTranslator();
        $node = $this->properties->getNode();
        $isPermissionGranted = $this->getSecurityManager()->isPermissionGranted('cms.widget.advanced.view');

        $parent = $this->getParent();
        $depth = $this->getDepth();
        $title = $this->getTitle();

        if ($parent) {
            try {
                $parentNode = $this->cms->getNode($node->getRootNodeId(), $node->getRevision(), $parent);
                $parent = $parentNode->getName($this->locale);
            } catch (NodeNotFoundException $exception) {
                $parent = 'unexistant';
            }
        } else {
            $nodeIds = $this->getNodeIds();
            $nodes = array();

            foreach ($nodeIds as $nodeId) {
                try {
                    $n = $this->cms->getNode($node->getRootNodeId(), $node->getRevision(), $nodeId);
                    $nodes[] = $n->getName($this->locale);
                } catch (NodeNotFoundException $exception) {

                }
            }
        }

        $preview = '';

        if ($parent) {
            $preview .= '<strong>' . $translator->translate('label.menu.parent') . '</strong>: ' . $parent . '<br>';
        } else {
            $preview .= '<strong>' . $translator->translate('label.menu.nodes') . '</strong>: ' . implode(', ', $nodes) . '<br>';
        }
        if ($isPermissionGranted) {
            $preview .= '<strong>' . $translator->translate('label.menu.depth') . '</strong>: ' . $depth . '<br>';
        }
        if ($title) {
            $preview .= '<strong>' . $translator->translate('label.title') . '</strong>: ' . $title . '<br>';
        }
        if ($isPermissionGranted) {
            $template = $this->getTemplate(static::TEMPLATE_NAMESPACE . '/default');
        } else {
            $template = $this->getTemplateName($this->getTemplate(static::TEMPLATE_NAMESPACE . '/default'));
        }
        $preview .= '<strong>' . $translator->translate('label.template') . '</strong>: ' . $template . '<br>';

        return $preview;
    }

    /**
     * Action to handle and show the properties of this widget
     * @return null
     */
    public function propertiesAction(ValidationFactory $validationFactory) {
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

        $title = $this->properties->getLocalizedWidgetProperty($this->locale, self::PROPERTY_TITLE);
        if ($title) {
            $showTitle = true;
            $title = $title == 1 ? '' : $title;
        } else {
            $showTitle = false;
            $title = null;
        }

        $data = array(
            self::PROPERTY_PARENT => $this->getParent(false),
            self::PROPERTY_NODES => $this->getNodeIds(),
            self::PROPERTY_DEPTH => $this->getDepth(),
            self::PROPERTY_TITLE . '-show' => $showTitle,
            self::PROPERTY_TITLE => $title,
            self::PROPERTY_TEMPLATE => $this->getTemplate(static::TEMPLATE_NAMESPACE . '/default'),
        );

        if ($data[self::PROPERTY_NODES]) {
            $data[self::PROPERTY_PARENT . '-select'] = self::PROPERTY_NODES;
        } else {
            $data[self::PROPERTY_PARENT . '-select'] = self::PROPERTY_PARENT;
        }

        $form = $this->createFormBuilder($data);
        $form->addRow(self::PROPERTY_PARENT . '-select', 'option', array(
            'label' => $translator->translate('label.menu.base'),
            'default' => 'parent',
            'options' => array(
                self::PROPERTY_PARENT => $translator->translate('label.menu.parent'),
                self::PROPERTY_NODES => $translator->translate('label.menu.nodes'),
            ),
            'attributes' => array(
                'data-toggle-dependant' => 'option-node-select',
            ),
            'validators' => array(
                'required' => array(),
            )
        ));
        $form->addRow(self::PROPERTY_PARENT, 'select', array(
            'label' => $translator->translate('label.menu.parent'),
            'description' => $translator->translate('label.menu.parent.description'),
            'options' => $nodeList,
            'attributes' => array(
                'class' => 'option-node-select option-node-select-' . self::PROPERTY_PARENT,
            ),
            'hide-optional' => true,
        ));
        $form->addRow(self::PROPERTY_NODES, 'select', array(
            'label' => $translator->translate('label.menu.nodes'),
            'description' => $translator->translate('label.menu.nodes.description'),
            'options' => $nodeList,
            'attributes' => array(
                'class' => 'option-node-select option-node-select-' . self::PROPERTY_NODES,
            ),
            'multiple' => true,
            'hide-optional' => true,
        ));
        $form->addRow(self::PROPERTY_DEPTH, 'select', array(
            'label' => $translator->translate('label.menu.depth'),
            'description' => $translator->translate('label.menu.depth.description'),
            'options' => $depths,
        ));
        $form->addRow(self::PROPERTY_TITLE . '-show', 'option', array(
            'label' => $translator->translate('label.title.show'),
            'description' => $translator->translate('label.menu.title.show.description'),
            'attributes' => array(
                'data-toggle-dependant' => 'option-title',
            ),
        ));
        $form->addRow(self::PROPERTY_TITLE, 'string', array(
            'label' => $translator->translate('label.title'),
            'description' => $translator->translate('label.menu.title.description'),
            'attributes' => array(
                'class' => 'option-title option-title-1',
            ),
        ));
        $form->addRow(self::PROPERTY_TEMPLATE, 'select', array(
            'label' => $translator->translate('label.template'),
            'options' => $this->getAvailableTemplates(static::TEMPLATE_NAMESPACE),
            'validators' => array(
                'required' => array(),
            ),
        ));

        $requiredValidator = $validationFactory->createValidator('required', array());

        $urlRequired = new ConditionalConstraint();
        $urlRequired->addValueCondition(self::PROPERTY_PARENT . '-select', self::PROPERTY_PARENT);
        $urlRequired->addValidator($requiredValidator, self::PROPERTY_PARENT);

        $fileRequired = new ConditionalConstraint();
        $fileRequired->addValueCondition(self::PROPERTY_PARENT . '-select', self::PROPERTY_NODES);
        $fileRequired->addValidator($requiredValidator, self::PROPERTY_NODES);

        $form->addValidationConstraint($urlRequired);
        $form->addValidationConstraint($fileRequired);

        $form = $form->build();
        if ($form->isSubmitted()) {
            if ($this->request->getBodyParameter('cancel')) {
                return false;
            }

            try {
                $form->validate();

                $data = $form->getData();

                if ($data[self::PROPERTY_TITLE]) {
                    $title = $data[self::PROPERTY_TITLE];
                } elseif ($data[self::PROPERTY_TITLE . '-show']) {
                    $title = '1';
                } else {
                    $title = null;
                }

                if ($data[self::PROPERTY_PARENT . '-select'] == self::PROPERTY_PARENT) {
                    $this->properties->setWidgetProperty(self::PROPERTY_PARENT, $data[self::PROPERTY_PARENT]);
                    $this->properties->setWidgetProperty(self::PROPERTY_NODES);
                } else {
                    $this->properties->setWidgetProperty(self::PROPERTY_PARENT);
                    $this->properties->setWidgetProperty(self::PROPERTY_NODES, implode(',', $data[self::PROPERTY_NODES]));
                }
                $this->properties->setWidgetProperty(self::PROPERTY_DEPTH, $data[self::PROPERTY_DEPTH]);
                $this->properties->setLocalizedWidgetProperty($this->locale, self::PROPERTY_TITLE, $title);

                $this->setTemplate($data[self::PROPERTY_TEMPLATE]);

                return true;
            } catch (ValidationException $exception) {
                $this->setValidationException($exception, $form);
            }
        }

        $view = $this->setTemplateView(static::TEMPLATE_NAMESPACE . '/properties', array(
            'form' => $form->getView(),
        ));
        $view->addJavascript('js/form.js');

        $form->processView($view);

        return false;
    }

    /**
     * Get the value for the parent node
     * @param boolean $fetchNodeId Set to false to skip the lookup of the node id
     * @return string
     */
    private function getParent($fetchNodeId = true) {
        $parent = $this->properties->getWidgetProperty(self::PROPERTY_PARENT);
        if (!$parent || !$fetchNodeId) {
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
     * Get the value for the selected nodes
     * @param boolean $fetchNodeId Set to false to skip the lookup of the node id
     * @return string
     */
    private function getNodeIds() {
        $nodes = $this->properties->getWidgetProperty(self::PROPERTY_NODES);
        if (!$nodes) {
            return array();
        }

        $nodeIds = explode(',', $nodes);

        $nodes = array();
        foreach ($nodeIds as $nodeId) {
            $nodes[$nodeId] = $nodeId;
        }

        return $nodes;
    }

    /**
     * Get the depth value
     * @return integer
     */
    private function getDepth() {
        return $this->properties->getWidgetProperty(self::PROPERTY_DEPTH, self::DEFAULT_DEPTH);
    }

    /**
     * Gets the title for this widget based on the properties
     * @param \ride\library\cms\node\node $parentNode
     * @return string|null
     */
    private function getTitle(Node $parentNode = null) {
        $title = $this->properties->getLocalizedWidgetProperty($this->locale, self::PROPERTY_TITLE);
        if ($title === null) {
            $title = $this->properties->getWidgetProperty(self::PROPERTY_TITLE);
        }

        if (!$title) {
            // no title, return null
            return null;
        } elseif ($title == 1) {
            // title is set to 1, use name of the parent node
            if (!$parentNode) {
                $parentNodeId = $this->getParent();
                if (!$parentNodeId) {
                    return null;
                }

                try {
                    $node = $this->properties->getNode();
                    $parentNode = $this->cms->getNode($node->getRootNodeId(), $node->getRevision(), $parentNodeId);
                } catch (NodeNotFoundException $exception) {
                    $this->getLog()->logException($exception);

                    return null;
                }
            }

            if ($parentNode) {
                $title = $parentNode->getName($this->locale);
            } else {
                $title = null;
            }
        }

        return $title;
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
