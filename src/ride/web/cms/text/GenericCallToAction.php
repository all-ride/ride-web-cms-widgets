<?php

namespace ride\web\cms\text;

/**
 * Generic call to action
 */
class GenericCallToAction implements CallToAction {

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
     * Suffix to append after the URL
     * @var string
     */
    protected $suffix;

    /**
     * Name of the icon
     * @var string
     */
    protected $type;

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

    /**
     * Sets the suffix
     * @param string $suffix
     * @return null
     */
    public function setSuffix($suffix) {
        $this->suffix = $suffix;
    }

    /**
     * Gets the suffix
     * @return string
     */
    public function getSuffix() {
        return $this->suffix;
    }

    /**
     * Sets the type
     * @param string $type
     * @return null
     */
    public function setType($type) {
        $this->type = $type;
    }

    /**
     * Gets the type
     * @return string
     */
    public function getType() {
        return $this->type;
    }

}
