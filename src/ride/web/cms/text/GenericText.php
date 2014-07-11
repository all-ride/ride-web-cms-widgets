<?php

namespace ride\web\cms\text;

use \InvalidArgumentException;

/**
 * Generic text data container
 */
class GenericText implements Text {

    /**
     * Name of the format
     * @var string
     */
    protected $format;

    /**
     * Title for the text
     * @var string
     */
    protected $title;

    /**
     * Body text
     * @var string
     */
    protected $body;

    /**
     * Path to a image
     * @var string
     */
    protected $image;

    /**
     * Alignment of the image
     * @var string
     */
    protected $imageAlignment;

    /**
     * Call to actions
     * @var array|null
     */
    protected $callToActions;

    /**
     * Constructs a new instance
     * @param string $format Name of the format
     * @param string $body Body text
     * @return null
     */
    public function __construct($format = null, $body = null) {
        $this->format = $format;
        $this->body = $body;

        $this->title = null;
        $this->subtitle = null;
        $this->image = null;
        $this->imageAlignment = null;
        $this->callToActions = array();
    }

    /**
     * Sets the name of the format
     * @param string $format Name of the format
     * @return null
     */
    public function setFormat($format) {
        $this->format = $format;
    }

    /**
     * Gets the name of the format
     * @return string
     */
    public function getFormat() {
        return $this->format;
    }

    /**
     * Sets the title
     * @param string $title
     * @return null
     */
    public function setTitle($title) {
        $this->title = $title;
    }

    /**
     * Gets the title
     * @return string
     */
    public function getTitle() {
        return $this->title;
    }

    /**
     * Sets the subtitle
     * @param string $subtitle
     * @return null
     */
    public function setSubtitle($subtitle) {
        $this->subtitle = $subtitle;
    }

    /**
     * Gets the subtitle
     * @return string
     */
    public function getSubtitle() {
        return $this->subtitle;
    }

    /**
     * Sets the body
     * @param string $body
     * @return null
     */
    public function setBody($body) {
        $this->body = $body;
    }

    /**
     * Gets the body
     * @return string
     */
    public function getBody() {
        return $this->body;
    }

    /**
     * Sets a image
     * @param string $image
     * @return null
     */
    public function setImage($image) {
        $this->image = $image;
    }

    /**
     * Gets the image
     * @return string
     */
    public function getImage() {
        return $this->image;
    }

    /**
     * Sets the image alignment
     * @param string $alignment
     * @return null
     */
    public function setImageAlignment($imageAlignment) {
        $this->imageAlignment = $imageAlignment;
    }

    /**
     * Gets the image alignment
     * @return string
     */
    public function getImageAlignment() {
        return $this->imageAlignment;
    }

    /**
     * Sets the call to actions
     * @param array|null $callToActions
     * @return null
     */
    public function setCallToActions(array $callToActions = array()) {
        foreach ($callToActions as $index => $callToAction) {
            if (!$callToAction instanceof CallToAction) {
                throw new InvalidArgumentException('Could not set call to actions: value on index ' . $index . ' is not an instance of CallToAction');
            }
        }

        $this->callToActions = $callToActions;
    }

    /**
     * Gets the call to actions
     * @return array|null
     */
    public function getCallToActions() {
        return $this->callToActions;
    }

}
