<?php

namespace ride\web\cms\text;

/**
 * Interface for a call to action
 */
interface CallToAction {

    /**
     * Sets the label
     * @param string $label
     * @return null
     */
    public function setLabel($label);

    /**
     * Gets the label
     * @return string
     */
    public function getLabel();

    /**
     * Sets the node
     * @param string $node Node ID
     * @return null
     */
    public function setNode($node);

    /**
     * Gets the node
     * @return string Node ID
     */
    public function getNode();

    /**
     * Sets the URL
     * @param string $url
     * @return null
     */
    public function setUrl($url);

    /**
     * Gets the url
     * @return string
     */
    public function getUrl();

    /**
     * Sets the type
     * @param string $type
     * @return null
     */
    public function setType($type);

    /**
     * Gets the type
     * @return string
     */
    public function getType();

}
