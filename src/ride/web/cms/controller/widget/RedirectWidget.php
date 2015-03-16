<?php

namespace ride\web\cms\controller\widget;

use ride\library\cms\node\exception\NodeNotFoundException;
use ride\library\cms\node\NodeModel;

/**
 * Widget to redirect the current page
 */
class RedirectWidget extends AbstractWidget {

    /**
     * Machine name of this widget
     * @var string
     */
    const NAME = 'redirect';

    /**
     * Path to the icon of this widget
     * @var string
     */
    const ICON = 'img/cms/widget/redirect.png';

    /**
     * Name of the property for the redirect node
     * @var string
     */
    const PROPERTY_NODE = 'node';

    /**
     * Name of the property for the redirect URL
     * @var string
     */
    const PROPERTY_URL = 'url';

    /**
     * Redirects the current node
     * @return null
     */
    public function indexAction(NodeModel $nodeModel) {
        $node = $this->properties->getNode();

        $url = $this->properties->getLocalizedWidgetProperty($this->locale, self::PROPERTY_URL);
        if ($url) {
            $url = $node->resolveUrl($this->locale, $this->request->getBaseScript(), $url);
        } else {
            $nodeId = $this->properties->getWidgetProperty(self::PROPERTY_NODE);
            if (!$nodeId) {
                return;
            }

            try {
                $node = $nodeModel->getNode($node->getRootNodeId(), $node->getRevision(), $nodeId);
            } catch (NodeNotFoundException $exception) {
                $this->getLog()->logException($exception);

                return;
            }

            $url = $node->getUrl($this->locale, $this->request->getBaseScript());
        }

        $this->response->setRedirect($url);
    }

    /**
     * Get a preview of the properties of this widget
     * @return string
     */
    public function getPropertiesPreview() {
        $translator = $this->getTranslator();
        $preview = '---';

        $url = $this->properties->getLocalizedWidgetProperty($this->locale, self::PROPERTY_URL);
        if ($url) {
            $preview = $translator->translate('label.url') . ': ' . $url;
        } else {
            $nodeId = $this->properties->getWidgetProperty(self::PROPERTY_NODE);
            if ($nodeId) {
                $nodeModel = $this->dependencyInjector->get('ride\\library\\cms\\node\\NodeModel');
                try {
                    $node = $this->properties->getNode();
                    $node = $nodeModel->getNode($node->getRootNodeId(), $node->getRevision(), $nodeId);

                    $node = $node->getName($this->locale);
                } catch (NodeNotFoundException $exception) {
                    $node = '---';
                }

                $preview = $translator->translate('label.node') . ': ' . $node;
            }
        }

        return $preview;
    }

    /**
     * Action to handle and show the properties of this widget
     * @return null
     */
    public function propertiesAction(NodeModel $nodeModel) {
        $translator = $this->getTranslator();
        $nodeList = $this->getNodeList($nodeModel);

        $data = array(
            self::PROPERTY_NODE => $this->properties->getWidgetProperty(self::PROPERTY_NODE),
            self::PROPERTY_URL => $this->properties->getLocalizedWidgetProperty($this->locale, self::PROPERTY_URL),
        );

        if ($data[self::PROPERTY_URL]) {
            $data['type'] = 'url';
        } elseif ($data[self::PROPERTY_NODE]) {
            $data['type'] = 'node';
        }

        $form = $this->createFormBuilder($data);
        $form->setId('form-redirect');
        $form->addRow('type', 'option', array(
            'label' => $translator->translate('label.type'),
            'options' => array(
                'url' => $translator->translate('label.url'),
                'node' => $translator->translate('label.node'),
            ),
            'attributes' => array(
                'data-toggle-dependant' => 'option-type',
            ),
            'validators' => array(
                'required' => array(),
            ),
        ));
        $form->addRow(self::PROPERTY_NODE, 'select', array(
            'label' => $translator->translate('label.node'),
            'options' => $nodeList,
            'attributes' => array(
                'class' => 'option-type option-type-node',
            ),
        ));
        $form->addRow(self::PROPERTY_URL, 'string', array(
            'label' => $translator->translate('label.url'),
            'attributes' => array(
                'class' => 'option-type option-type-url',
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

                $this->properties->setWidgetProperty(self::PROPERTY_NODE, $data[self::PROPERTY_NODE]);
                $this->properties->setLocalizedWidgetProperty($this->locale, self::PROPERTY_URL, $data[self::PROPERTY_URL]);

                return true;
            } catch (ValidationException $e) {

            }
        }

        $view = $this->setTemplateView('cms/widget/redirect/properties', array(
            'form' => $form->getView(),
        ));

        $form->processView($view);

        return false;
    }

}
