<?php

namespace ride\web\cms\controller\widget;

use ride\library\http\Response;
use ride\library\router\Route;
use ride\library\system\file\browser\FileBrowser;
use ride\library\validation\exception\ValidationException;

use ride\web\cms\form\FileComponent;

/**
 * Widget to offer some files as download
 */
class FilesWidget extends AbstractWidget implements StyleWidget {

    /**
     * Machine name of this widget
     * @var string
     */
    const NAME = 'files';

    /**
     * Path to the icon of this widget
     * @var string
     */
    const ICON = 'img/cms/widget/files.png';

    /**
     * Name of the files property
     * @var string
     */
    const PROPERTY_FILES = 'files';

    /**
     * Namespace for the templates of this widget
     * @var string
     */
    const TEMPLATE_NAMESPACE = 'cms/widget/files';

    /**
     * Gets the routes for this widget
     * @return array
     */
    public function getRoutes() {
        return array(
            new Route('/download/%widgetId%/%fileId%', array($this, 'downloadAction'), 'download'),
        );
    }

    /**
     * Sets a title view to the response
     * @return null
     */
    public function indexAction() {
        $title = $this->properties->getLocalizedWidgetProperty($this->locale, self::PROPERTY_TITLE);
        $files = $this->getFiles();
        foreach ($files as $fileId => $file) {
            $files[$fileId]['url'] = $this->getUrl('download', array(
                'widgetId' => $this->id,
                'fileId' => $fileId,
            ));
        }

        $this->setTemplateView($this->getTemplate(static::TEMPLATE_NAMESPACE . '/default'), array(
            'title' => $title,
            'files' => $files,
        ));
    }

    /**
     * Action to download the file
     * @param string $widgetId
     * @param string $fileId
     * @return null
     */
    public function downloadAction(FileBrowser $fileBrowser, $widgetId, $fileId) {
        if ($widgetId != $this->id) {
            return;
        }

        $files = $this->getFiles();
        if (!isset($files[$fileId])) {
            $this->response->setStatusCode(Response::STATUS_CODE_NOT_FOUND);

            return;
        }

        $file = $fileBrowser->getFile($files[$fileId]['file']);
        if (!$file) {
            $this->response->setStatusCode(Response::STATUS_CODE_NOT_FOUND);

            return;
        }

        $this->setDownloadView($file, $file->getName());
    }

    /**
     * Gets a preview of the properties
     * @return string
     */
    public function getPropertiesPreview() {
        $translator = $this->getTranslator();
        $preview = '';

        $title = $this->properties->getLocalizedWidgetProperty($this->locale, self::PROPERTY_TITLE);
        if ($title) {
            $preview .= '<strong>' . $translator->translate('label.title') . '</strong>: ' . $title . '<br>';
        }
        
        if ($this->getSecurityManager()->isPermissionGranted('cms.advanced')) {
            $template = $this->getTemplate(static::TEMPLATE_NAMESPACE . '/default');
        } else {
            $template = $this->getTemplateName($this->getTemplate(static::TEMPLATE_NAMESPACE . '/default'));
        }
        
        $preview .= '<strong>' . $translator->translate('label.template') . '</strong>: ' . $template . '<br>';

        $files = $this->getFiles();
        if ($files) {
            $preview .= '<ul>';

            foreach ($files as $file) {
                $preview .= '<li>' . $file['label'] . '</li>';
            }

            $preview .= '</ul>';
        }

        return $preview;
    }

    /**
     * Action to handle and show the properties of this widget
     * @return null
     */
    public function propertiesAction() {
        $translator = $this->getTranslator();

        $data = array(
            self::PROPERTY_TITLE => $this->properties->getLocalizedWidgetProperty($this->locale, self::PROPERTY_TITLE),
            self::PROPERTY_FILES => $this->getFiles(),
            self::PROPERTY_TEMPLATE => $this->getTemplate(static::TEMPLATE_NAMESPACE . '/default'),
        );

        $numFiles = count($data['files']);

        $form = $this->createFormBuilder($data);
        $form->addRow(self::PROPERTY_TITLE, 'string', array(
            'label' => $translator->translate('label.title'),
            'filters' => array(
                'trim' => array(),
            ),
        ));
        $form->addRow(self::PROPERTY_FILES, 'collection', array(
            'label' => $translator->translate('label.files'),
            'type' => 'component',
            'order' => true,
            'options' => array(
                'component' => new FileComponent(),
            ),
        ));
        $form->addRow(self::PROPERTY_TEMPLATE, 'select', array(
            'label' => $translator->translate('label.template'),
            'description' => $translator->translate('label.template.widget.description'),
            'options' => $this->getAvailableTemplates(static::TEMPLATE_NAMESPACE),
            'validators' => array(
                'required' => array(),
            ),
        ));

        $form = $form->build();
        if ($form->isSubmitted()) {
            if ($this->request->getBodyParameter('cancel')) {
                return false;
            }

            try {
                $form->validate();

                $data = $form->getData();

                $this->properties->setLocalizedWidgetProperty($this->locale, self::PROPERTY_TITLE, $data[self::PROPERTY_TITLE] ? $data[self::PROPERTY_TITLE] : null);

                $index = 1;
                foreach ($data[self::PROPERTY_FILES] as $file) {
                    if (!$file['label']) {
                        $file['label'] = substr($file['file'], strrpos($file['file'], '/') + 1);
                    }

                    $this->properties->setWidgetProperty(self::PROPERTY_FILES . '.' . $this->locale . '.' . $index . '.file', $file['file']);
                    $this->properties->setWidgetProperty(self::PROPERTY_FILES . '.' . $this->locale . '.' . $index . '.label', $file['label']);
                    $this->properties->setWidgetProperty(self::PROPERTY_FILES . '.' . $this->locale . '.' . $index . '.image', $file['image']);

                    $index++;
                }

                while ($index <= $numFiles) {
                    $this->properties->setWidgetProperty(self::PROPERTY_FILES . '.' . $this->locale . '.' . $index . '.file', null);
                    $this->properties->setWidgetProperty(self::PROPERTY_FILES . '.' . $this->locale . '.' . $index . '.label', null);
                    $this->properties->setWidgetProperty(self::PROPERTY_FILES . '.' . $this->locale . '.' . $index . '.image', null);

                    $index++;
                }

                $this->setTemplate($data[self::PROPERTY_TEMPLATE]);

                return true;
            } catch (ValidationException $exception) {
                $this->setValidationException($exception, $form);
            }
        }

        $view = $this->setTemplateView(static::TEMPLATE_NAMESPACE . '/properties', array(
            'form' => $form->getView(),
        ));
        $form->processView($view);

        return false;
    }

    /**
     * Gets the files from the properties
     * @return array
     */
    protected function getFiles() {
        $prefix = self::PROPERTY_FILES . '.' . $this->locale . '.';

        $result = array();

        $properties = $this->properties->getWidgetProperties($prefix);
        foreach ($properties as $key => $value) {
            $key = str_replace($prefix, '', $key);

            list($index, $property) = explode('.', $key);

            if (isset($result[$index])) {
                $result[$index][$property] = $value;
            } else {
                $result[$index] = array(
                    $property => $value,
                );
            }
        }

        return $result;
    }

    /**
     * Gets the options for the styles
     * @return array Array with the name of the option as key and the
     * translation key as value
     */
    public function getWidgetStyleOptions() {
        return array(
            'container' => 'label.style.container',
            'title' => 'label.style.title',
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
