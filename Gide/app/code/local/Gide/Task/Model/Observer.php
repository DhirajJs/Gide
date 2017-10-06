<?php


/**
 *
 *
 * @author      dhiraj G <dhirajmetal@gmail.com>
 */
class Gide_Task_Model_Observer
{

    public function addJsonSnippet($observer)
    {
        /** @var Mage_Core_Controller_Request_Http $action */
        $action = Mage::app()->getRequest();
        /** @var Mage_Core_Model_Layout $layout */
        $layout = $observer->getLayout();

        $controllerName = $action->getControllerName();
        $controllerAction = $action->getActionName();
        if ($controllerName == 'product' && $controllerAction == 'view') {
            $this->_appendSnippet($layout);
        }
    }
    
    private function _appendSnippet($layout)
    {
        $update = $layout->getUpdate();
        //used a handler here as maybe in the future we can inject the snippet via layout xml instead of observer
        $update->addHandle('handler_jsonsnippet');
        $update->load();

    }
}
