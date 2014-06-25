<?php

namespace ride\web\cms\controller\widget;

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
     * Gets the templates of this widget
     * @return array|null
     */
    public function getTemplates() {
        return null;
    }

    /**
     * Redirects the current node
     * @return null
     */
    public function indexAction(NodeModel $nodeModel) {
        $url = $this->getUrl();
        if ($url) {
            $url = $this->properties->getNode()->resolveUrl($this->locale, $this->request->getBaseScript(), $url);
        } else {
            $nodeId = $this->properties->getWidgetProperty(self::PROPERTY_NODE);
            if (!$nodeId) {
                return;
            }

            $node = $nodeModel->getNode($nodeId);

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

        $url = $this->getUrl();
        if ($url) {
            $preview = $translator->translate('label.url') . ': ' . $url;
        } else {
            $nodeId = $this->properties->getWidgetProperty(self::PROPERTY_NODE);
            if ($nodeId) {
                $nodeModel = $this->dependencyInjector->get('ride\\library\\cms\\node\\NodeModel');
                $node = $nodeModel->getNode($nodeId);
                $preview = $translator->translate('label.node') . ': ' . $node->getName($this->locale);
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
            self::PROPERTY_URL => $this->getUrl(),
        );

        if ($data[self::PROPERTY_URL]) {
            $data['type'] = 'url';
        } elseif ($data[self::PROPERTY_NODE]) {
            $data['type'] = 'node';
        }

        $form = $this->createFormBuilder($data);
        $form->setId('form-redirect');
        $form->addRow('type', 'option', array(
            'options' => array(
                'url' => $translator->translate('label.url'),
                'node' => $translator->translate('label.node'),
            ),
            'validators' => array(
                'required' => array(),
            ),
        ));
        $form->addRow(self::PROPERTY_NODE, 'select', array(
            'label' => $translator->translate('label.node'),
            'options' => $nodeList,
        ));
        $form->addRow(self::PROPERTY_URL, 'string', array(
            'label' => $translator->translate('label.url'),
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
                $this->properties->setWidgetProperty(self::PROPERTY_URL . '.' . $this->locale, $data[self::PROPERTY_URL]);

                return true;
            } catch (ValidationException $e) {

            }
        }

        $view = $this->setTemplateView('cms/widget/redirect.properties', array(
            'form' => $form->getView(),
        ));
        $view->addJavascript('js/cms/redirect.js');

        return false;
    }

    /**
     * Gets the URL from the widget properties
     * @return string|null
     */
    protected function getUrl() {
        $url = $this->properties->getWidgetProperty(self::PROPERTY_URL . '.' . $this->locale);
        if (!$url) {
            $url = $this->properties->getWidgetProperty(self::PROPERTY_URL);
        }

        return $url;
    }

}
