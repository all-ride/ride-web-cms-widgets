<?php

namespace ride\web\cms\controller\widget;

use ride\library\cms\node\NodeModel;
use ride\library\http\Response;
use ride\library\mail\transport\Transport;
use ride\library\validation\exception\ValidationException;

use ride\web\form\component\HoneyPotComponent;
use ride\web\form\exception\HoneyPotException;
use ride\web\cms\controller\widget\AbstractWidget;

/**
 * Widget to handle a contact form
 */
class ContactWidget extends AbstractWidget implements StyleWidget {

    /**
     * Machine name of this widget
     * @var string
     */
    const NAME = 'contact';

    /**
     * Path to the icon of this widget
     * @var string
     */
    const ICON = 'img/cms/widget/contact.png';

    /**
     * Name of the recipient property
     * @var string
     */
    const PROPERTY_RECIPIENT = 'recipient';

    /**
     * Name of the BCC property
     * @var string
     */
    const PROPERTY_BCC = 'bcc';

    /**
     * Name of the subject property
     * @var string
     */
    const PROPERTY_SUBJECT = 'subject';

    /**
     * Name of the finish node property
     * @var string
     */
    const PROPERTY_FINISH_NODE = 'finish.node';

    /**
     * Namespace for the templates of this widget
     * @var string
     */
    const TEMPLATE_NAMESPACE = 'cms/widget/contact';

    /**
     * Action to show and handle the contact form
     * @return null
     */
    public function indexAction(HoneyPotComponent $honeyPotComponent, Transport $transport) {
        $recipient = $this->properties->getLocalizedWidgetProperty($this->locale, self::PROPERTY_RECIPIENT);
        if (!$recipient) {
            return;
        }

        $translator = $this->getTranslator();

        $attributes = array(
            'name' => array(),
            'email' => array(),
            'message' => array(),
        );
        if ($this->properties->getWidgetProperty('compact')) {
            $attributes['name']['placeholder'] = $translator->translate('label.name');
            $attributes['email']['placeholder'] = $translator->translate('label.email');
            $attributes['message']['placeholder'] = $translator->translate('label.message');
        }

        $form = $this->createFormBuilder();
        $form->setAction('contact');
        $form->addRow('name', 'string', array(
            'label' => $translator->translate('label.name'),
            'attributes' => $attributes['name'],
            'filters' => array(
                'trim' => array(),
            ),
            'validators' => array(
                'required' => array(),
            ),
        ));
        $form->addRow('email', 'email', array(
            'label' => $translator->translate('label.email'),
            'attributes' => $attributes['email'],
            'filters' => array(
                'trim' => array(),
            ),
            'validators' => array(
                'required' => array(),
            ),
        ));
        $form->addRow('message', 'text', array(
            'label' => $translator->translate('label.message'),
            'attributes' => $attributes['message'],
            'filters' => array(
                'trim' => array(),
            ),
            'validators' => array(
                'required' => array(),
            ),
        ));
        $form->addRow('phone', 'component', array(
            'component' => $honeyPotComponent,
            'embed' => true,
        ));

        $form = $form->build();
        if ($form->isSubmitted()) {
            try {
                $form->validate();

                $data = $form->getData();

                $this->sendMail($data, $recipient, $transport);

                $finish = $this->properties->getLocalizedWidgetProperty($this->locale, self::PROPERTY_FINISH_NODE);
                if ($finish) {
                    $url = $this->getUrl('cms.front.' . $this->properties->getNode()->getRootNodeId() . '.' . $finish . '.' . $this->locale);
                } else {
                    $url = $this->request->getUrl();

                    $this->addSuccess('success.message.sent');
                }

                $this->response->setRedirect($url);

                return;
            } catch (ValidationException $exception) {
                $this->setValidationException($exception, $form);
            } catch (HoneyPotException $exception) {
                $this->addError('error.honeypot');
            }
        }

        $view = $this->setTemplateView($this->getTemplate(static::TEMPLATE_NAMESPACE . '/default'), array(
            'form' => $form->getView(),
        ));

        $form->processView($view);
    }

    /**
     * Function to handle the sending of contact Email
     * @param $data
     * @param $recipient
     * @param Transport $transport
     * @throws \ride\library\mail\exception\MailException
     */
    public function sendMail($data, $recipient, Transport $transport) {
        $message = $transport->createMessage();
        $message->setFrom($data['name'] . ' <' . $data['email'] . '>');
        $message->setTo($recipient);
        $message->setSubject($this->getSubject(true));
        $message->setReplyTo($data['email']);
        $message->setReturnPath($data['email']);
        $message->setMessage($data['message']);

        $bcc = $this->properties->getLocalizedWidgetProperty($this->locale, self::PROPERTY_BCC);
        if ($bcc) {
            $message->setBcc(explode(',', $bcc));
        }

        $transport->send($message);

    }

    /**
     * Get a preview of the properties of this widget
     * @return string
     */
    public function getPropertiesPreview() {
        $translator = $this->getTranslator();
        $preview = '';

        $recipient = $this->properties->getLocalizedWidgetProperty($this->locale, self::PROPERTY_RECIPIENT);
        if ($recipient) {
            $preview .= '<strong>' . $translator->translate('label.recipient') . '</strong>: ' . $recipient . '<br>';
        }
        $bcc = $this->properties->getLocalizedWidgetProperty($this->locale, self::PROPERTY_BCC);
        if ($bcc) {
            $preview .= '<strong>' . $translator->translate('label.bcc') . '</strong>: ' . str_replace(',', ', ', $bcc) . '<br>';
        }

        $subject = $this->getSubject();
        if ($subject) {
            $preview .= '<strong>' . $translator->translate('label.subject') . '</strong>: ' . $subject . '<br>';
        }

        $finish = $this->properties->getLocalizedWidgetProperty($this->locale, self::PROPERTY_FINISH_NODE);
        if ($finish) {
            $preview .= '<strong>' . $translator->translate('label.node.finish') . '</strong>: ' . $finish . '<br>';
        }

        $preview .= '<strong>' . $translator->translate('label.template') . '</strong>: ' . $this->getTemplate(static::TEMPLATE_NAMESPACE . '/default') . '<br>';

        return $preview;
    }

    /**
     * Action to handle and show the properties of this widget
     * @return null
     */
    public function propertiesAction(NodeModel $nodeModel) {
        $translator = $this->getTranslator();

        $bcc = $this->properties->getLocalizedWidgetProperty($this->locale, self::PROPERTY_BCC);
        if ($bcc) {
            $bcc = explode(',', $bcc);
        }

        $data = array(
            self::PROPERTY_RECIPIENT => $this->properties->getLocalizedWidgetProperty($this->locale, self::PROPERTY_RECIPIENT),
            self::PROPERTY_BCC => $bcc,
            self::PROPERTY_SUBJECT => $this->getSubject(),
            'finishNode' => $this->properties->getLocalizedWidgetProperty($this->locale, self::PROPERTY_FINISH_NODE),
            self::PROPERTY_TEMPLATE => $this->getTemplate(static::TEMPLATE_NAMESPACE . '/default'),
        );

        $form = $this->createFormBuilder($data);
        $form->addRow(self::PROPERTY_SUBJECT, 'string', array(
            'label' => $translator->translate('label.subject'),
            'filters' => array(
                'trim' => array(),
            ),
        ));
        $form->addRow(self::PROPERTY_RECIPIENT, 'email', array(
            'label' => $translator->translate('label.recipient'),
            'filters' => array(
                'trim' => array(),
            ),
            'validators' => array(
                'required' => array(),
            ),
        ));
        $form->addRow(self::PROPERTY_BCC, 'collection', array(
            'label' => $translator->translate('label.bcc'),
            'type' => 'email',
            'filters' => array(
                'trim' => array(),
            ),
        ));
        $form->addRow('finishNode', 'select', array(
            'label' => $translator->translate('label.node.finish'),
            'description' => $translator->translate('label.node.finish.description'),
            'options' => $this->getNodeList($nodeModel),
        ));
        $form->addRow(self::PROPERTY_TEMPLATE, 'select', array(
            'label' => $translator->translate('label.template'),
            'options' => $this->getAvailableTemplates(static::TEMPLATE_NAMESPACE),
            'validators' => array(
                'required' => array(),
            ),
        ));

        $form = $form->build();
        if ($form->isSubmitted()) {
            try {
                $form->validate();

                $data = $form->getData();

                $this->properties->setLocalizedWidgetProperty($this->locale, self::PROPERTY_RECIPIENT, $data[self::PROPERTY_RECIPIENT]);
                $this->properties->setLocalizedWidgetProperty($this->locale, self::PROPERTY_BCC, implode(',', $data['bcc']));
                $this->properties->setLocalizedWidgetProperty($this->locale, self::PROPERTY_SUBJECT, $data[self::PROPERTY_SUBJECT]);
                $this->properties->setLocalizedWidgetProperty($this->locale, self::PROPERTY_FINISH_NODE, $data['finishNode']);

                $this->setTemplate($data[self::PROPERTY_TEMPLATE]);

                return true;
            } catch (ValidationException $exception) {
                $this->setValidationException($exception, $form);
            }
        }

        $this->setTemplateView(static::TEMPLATE_NAMESPACE . '/properties', array(
            'form' => $form->getView(),
        ));

        return false;
    }

    /**
     * Gets the subject for the message
     * @param boolean $useDefault Set to true to return a default subject
     * @return string
     */
    protected function getSubject($useDefault = false) {
        $subject = $this->properties->getLocalizedWidgetProperty($this->locale, self::PROPERTY_SUBJECT);
        if (!$subject && $useDefault) {
            $subject = 'Message from ' . $this->properties->getNode()->getRootNode()->getName($this->locale);
        }

        return $subject;
    }

    /**
     * Gets the options for the styles
     * @return array Array with the name of the option as key and the
     * translation key as value
     */
    public function getWidgetStyleOptions() {
        return array(
            'container' => 'label.style.container',
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
