<?php

namespace ride\web\cms;

use ride\library\cms\node\PageNode;
use ride\library\event\Event;

use ride\web\cms\controller\widget\TitleWidget;
use ride\web\cms\view\NodeTemplateView;

/**
 * Application listener for the menu widget to apply the to add processing for the CMS widgets
 */
class MenuWidgetApplicationListener {

    /**
     * Hook to perform extra processing for the CMS frontend template views
     * @param \ride\library\event\Event $event Triggered event
     * @return null
     */
    public function prepareTemplateView(Event $event) {
        $web = $event->getArgument('web');
        $response = $web->getResponse();
        if (!$response) {
            return;
        }

        $view = $response->getView();
        if (!$view instanceof NodeTemplateView) {
            return;
        }

        $node = $view->getNode();

        $nodes = $node->getContext('title.nodes', array());

        $node->setContext('title.nodes', $this->applyHierarchy($nodes));
    }

    /**
     * Applies the hierarchy of the provided title nodes
     * @param array $nodes Title nodes
     */
    private function applyHierarchy(array $nodes) {
        $menu = new PageNode();
        $parents = array();
        $parent = $menu;

        $currentHeading = 2;
        $previousNode = null;

        foreach ($nodes as $node) {
            $nodeHeading = $node->get(TitleWidget::PROPERTY_HEADING, 2);

            if ($nodeHeading < 2) {
                // at least level 2
                continue;
            } elseif ($nodeHeading == $currentHeading + 1) {
                // increase heading level
                array_push($parents, $parent);
                $parent = $previousNode;
                $currentHeading++;
            } elseif ($nodeHeading < $currentHeading) {
                // decrease heading level
                do {
                    $parent = array_pop($parents);
                    $currentHeading--;
                } while ($currentHeading != $nodeHeading);
            } elseif ($nodeHeading != $currentHeading) {
                // skip jumping multiple heading levels at once
                continue;
            }

            $children = $parent->getChildren();
            $children[] = $node;

            $parent->setChildren($children);

            $previousNode = $node;
        }

        return $menu->getChildren();
    }

}
