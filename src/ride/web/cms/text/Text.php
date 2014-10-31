<?php

namespace ride\web\cms\text;

/**
 * Interface for text
 */
interface Text {

    /**
     * Left alignment
     * @var string
     */
    const ALIGN_LEFT = 'left';

    /**
     * Right alignment
     * @var string
     */
    const ALIGN_RIGHT = 'right';

    /**
     * No alignment
     * @var string
     */
    const ALIGN_NONE = 'none';

    /**
     * Justify alignment
     * @var string
     */
    const ALIGN_JUSTIFY = 'justify';

    /**
     * Sets the name of the format
     * @param string $format Name of the format
     * @return null
     */
    public function setFormat($format);

    /**
     * Gets the name of the format
     * @return string
     */
    public function getFormat();

    /**
     * Sets the title
     * @param string $title
     * @return null
     */
    public function setTitle($title);

    /**
     * Gets the title
     * @return string
     */
    public function getTitle();

    /**
     * Sets the subtitle
     * @param string $subtitle
     * @return null
     */
    public function setSubtitle($subtitle);

    /**
     * Gets the subtitle
     * @return string
     */
    public function getSubtitle();

    /**
     * Sets the body
     * @param string $body
     * @return null
     */
    public function setBody($body);

    /**
     * Gets the body
     * @return string
     */
    public function getBody();

    /**
     * Sets a image
     * @param string $image
     * @return null
     */
    public function setImage($image);

    /**
     * Gets the image
     * @return string
     */
    public function getImage();

    /**
     * Sets the image alignment
     * @param string $alignment
     * @return null
     */
    public function setImageAlignment($imageAlignment);

    /**
     * Gets the image alignment
     * @return string
     */
    public function getImageAlignment();

    /**
     * Sets the call to actions
     * @param array $callToActions
     * @return null
     */
    public function setCallToActions(array $callToActions = array());

    /**
     * Gets the call to actions
     * @return array
     */
    public function getCallToActions();

}
