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
     * Namespace for the templates of this widget
     * @var string
     */
    const TEMPLATE_NAMESPACE = 'cms/widget/contact';

    /**
     * Action to show and handle the contact form
     * @return null
     */
    public function indexAction(HoneyPotComponent $honeyPotComponent, Transport $transport) {
        $recipient = $this->getRecipient();
        if (!$recipient) {
            return;
        }

        $translator = $this->getTranslator();

        $form = $this->createFormBuilder();
        $form->addRow('name', 'string', array(
            'label' => $translator->translate('label.name'),
            'filters' => array(
                'trim' => array(),
            ),
            'validators' => array(
                'required' => array(),
            ),
        ));
        $form->addRow('email', 'email', array(
            'label' => $translator->translate('label.email'),
            'filters' => array(
                'trim' => array(),
            ),
            'validators' => array(
                'required' => array(),
            ),
        ));
        $form->addRow('message', 'text', array(
            'label' => $translator->translate('label.message'),
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

                $message = $transport->createMessage();
                $message->setFrom($data['name'] . ' <' . $data['email'] . '>');
                $message->setTo($recipient);
                $message->setSubject($this->getSubject(true));
                $message->setReplyTo($data['email']);
                $message->setReturnPath($data['email']);
                $message->setMessage($data['message']);

                $transport->send($message);

                $finish = $this->properties->getWidgetProperty('finish.node');
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
     * Get a preview of the properties of this widget
     * @return string
     */
    public function getPropertiesPreview() {
        $translator = $this->getTranslator();
        $preview = '';

        $recipient = $this->getRecipient();
        if ($recipient) {
            $preview .= '<strong>' . $translator->translate('label.recipient') . '</strong>: ' . $recipient . '<br />';
        }

        $subject = $this->getSubject();
        if ($subject) {
            $preview .= '<strong>' . $translator->translate('label.subject') . '</strong>: ' . $subject . '<br />';
        }

        $finish = $this->properties->getWidgetProperty('finish.node');
        if ($finish) {
            $preview .= '<strong>' . $translator->translate('label.node.finish') . '</strong>: ' . $finish . '<br />';
        }

        return $preview;
    }

    /**
     * Action to handle and show the properties of this widget
     * @return null
     */
    public function propertiesAction(NodeModel $nodeModel) {
        $translator = $this->getTranslator();

        $data = array(
            'recipient' => $this->getRecipient(),
            'subject' => $this->getSubject(),
            'finishNode' => $this->properties->getWidgetProperty('finish.node'),
            self::PROPERTY_TEMPLATE => $this->getTemplate(static::TEMPLATE_NAMESPACE . '/default'),
        );

        $form = $this->createFormBuilder($data);
        $form->addRow('recipient', 'email', array(
            'label' => $translator->translate('label.recipient'),
            'filters' => array(
                'trim' => array(),
            ),
            'validators' => array(
                'required' => array(),
            ),
        ));
        $form->addRow('subject', 'string', array(
            'label' => $translator->translate('label.subject'),
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

                $this->properties->setWidgetProperty('recipient.' . $this->locale, $data['recipient']);
                $this->properties->setWidgetProperty('subject.' . $this->locale, $data['subject']);
                $this->properties->setWidgetProperty('finish.node', $data['finishNode']);

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
     * Gets the recipient for the message
     * @return string
     */
    protected function getRecipient() {
        $recipient = $this->properties->getWidgetProperty('recipient.' . $this->locale);
        if (!$recipient) {
            $recipient = $this->properties->getWidgetProperty('recipient');
        }

        return $recipient;
    }

    /**
     * Gets the subject for the message
     * @param boolean $useDefault Set to true to return a default subject
     * @return string
     */
    protected function getSubject($useDefault = false) {
        $subject = $this->properties->getWidgetProperty('subject.' . $this->locale);
        if (!$subject) {
            $subject = $this->properties->getWidgetProperty('subject');
        }

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
            'container' => 'label.widget.style.container',
        );
    }

}
