<?php

namespace ride\web\cms\text;

/**
 * Generic call to action
 */
class GenericCallToAction implements CallToAction {

    /**
     * Name of the icon
     * @var string
     */
    protected $icon;

    /**
     * Label for the action
     * @var string
     */
    protected $label;

    /**
     * Node id to link the action
     * @var string
     */
    protected $node;

    /**
     * URL to link the action
     * @var string
     */
    protected $url;

    /**
     * Sets the icon
     * @param string $icon
     * @return null
     */
    public function setIcon($icon) {
        $this->icon = $icon;
    }

    /**
     * Gets the icon
     * @return string
     */
    public function getIcon() {
        return $this->icon;
    }

    /**
     * Sets the label
     * @param string $label
     * @return null
     */
    public function setLabel($label) {
        $this->label = $label;
    }

    /**
     * Gets the label
     * @return string
     */
    public function getLabel() {
        return $this->label;
    }

    /**
     * Sets the node
     * @param string $node Node ID
     * @return null
     */
    public function setNode($node) {
        $this->node = $node;
    }

    /**
     * Gets the node
     * @return string Node ID
     */
    public function getNode() {
        return $this->node;
    }

    /**
     * Sets the URL
     * @param string $url
     * @return null
     */
    public function setUrl($url) {
        $this->url = $url;
    }

    /**
     * Gets the url
     * @return string
     */
    public function getUrl() {
        return $this->url;
    }

}
