<?php

namespace ride\web\cms\text;

/**
 * Interface for a call to action
 */
interface CallToAction {

    /**
     * Sets the icon
     * @param string $icon
     * @return null
     */
    public function setIcon($icon);

    /**
     * Gets the icon
     * @return string
     */
    public function getIcon();

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

}
