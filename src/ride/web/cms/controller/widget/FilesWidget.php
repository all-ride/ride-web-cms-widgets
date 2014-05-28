<?php

namespace ride\web\cms\controller\widget;

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
     * Path to the template of the widget view
     * @var string
     */
    const TEMPLATE = 'cms/widget/files/files';

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
        $title = $this->properties->getWidgetProperty('title.' . $this->locale);
        $files = $this->getFiles();
        foreach ($files as $fileId => $file) {
            $files[$fileId]['url'] = $this->getUrl('download', array(
                'widgetId' => $this->id,
                'fileId' => $fileId,
            ));
        }

        $this->setTemplateView(static::TEMPLATE, array(
            'title' => $title,
            'files' => $files,
        ));

        if ($this->properties->isAutoCache()) {
            $this->properties->setCache(true);
        }
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

        $this->setDownloadView($file);
    }

    /**
     * Gets a preview of the properties
     * @return string
     */
    public function getPropertiesPreview() {
        $preview = '';

        $title = $this->properties->getWidgetProperty('title.' . $this->locale);
        if ($title) {
            $preview .= '<strong>' . $title . '</strong><br>';
        }

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
            'title' => $this->properties->getWidgetProperty('title.' . $this->locale),
            'files' => $this->getFiles(),
        );

        $numFiles = count($data['files']);

        $form = $this->createFormBuilder($data);
        $form->addRow('title', 'string', array(
            'label' => $translator->translate('label.title'),
            'filters' => array(
                'trim' => array(),
            ),
        ));
        $form->addRow('files', 'collection', array(
            'label' => $translator->translate('label.files'),
            'type' => 'component',
            'options' => array(
                'component' => new FileComponent(),
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

                $this->properties->setWidgetProperty('title.' . $this->locale, $data['title']);

                $index = 1;
                foreach ($data['files'] as $file) {
                    if (!$file['label']) {
                        $file['label'] = substr($file['file'], strrpos($file['file'], '/') + 1);
                    }

                    $this->properties->setWidgetProperty('files.' . $this->locale . '.' . $index . '.file', $file['file']);
                    $this->properties->setWidgetProperty('files.' . $this->locale . '.' . $index . '.label', $file['label']);
                    $this->properties->setWidgetProperty('files.' . $this->locale . '.' . $index . '.image', $file['image']);

                    $index++;
                }

                while ($index <= $numFiles) {
                    $this->properties->setWidgetProperty('files.' . $this->locale . '.' . $index . '.file', null);
                    $this->properties->setWidgetProperty('files.' . $this->locale . '.' . $index . '.label', null);
                    $this->properties->setWidgetProperty('files.' . $this->locale . '.' . $index . '.image', null);

                    $index++;
                }

                return true;
            } catch (ValidationException $exception) {
                $this->setValidationException($exception, $form);
            }
        }

        $view = $this->setTemplateView('cms/widget/files/properties', array(
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
        $result = array();

        $properties = $this->properties->getWidgetProperties();
        foreach ($properties as $key => $value) {
            $prefix = 'files.' . $this->locale . '.';

            if (strpos($key, $prefix) !== 0) {
                continue;
            }

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
            'container' => 'label.widget.style.container',
            'title' => 'label.widget.style.title',
        );
    }

}
