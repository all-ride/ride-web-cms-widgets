<?php

namespace ride\web\cms\form;

use ride\library\config\Config;
use ride\library\form\component\AbstractComponent;
use ride\library\form\FormBuilder;


/**
 * Form component to add a social media account to link to
 */
class SocialMediaComponent extends AbstractComponent {

    /**
     * @var Config $config ;
     */
    protected $config;

    /**
     * @param \ride\library\config\Config $config
     */
    public function __construct(Config $config) {
        $this->config = $config;
    }

    /**
     * Prepares the form
     * @param \ride\library\form\FormBuilder $builder
     * @param array $options
     * @return null
     */
    public function prepareForm(FormBuilder $builder, array $options) {
        $translator = $options['translator'];
        $socialMedia = $this->config->get('social');

        $builder->addRow('socialMediaName', 'select', array(
            'label'    => $translator->translate('label.social.media.name'),
            'options'  => array_combine(array_keys($socialMedia), array_keys($socialMedia)),
            'widget'   => 'select',
            'required' => array(),
        ));
        $builder->addRow('accountName', 'string', array(
            'label'    => $translator->translate('label.social.media.account.name'),
            'required' => array(),
        ));
    }
}
