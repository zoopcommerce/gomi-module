<?php

namespace Zoop\GomiModule\Controller\Listener;

use Zend\Mvc\MvcEvent;
use Zend\View\Model\JsonModel;
use Zoop\ShardModule\Controller\Listener\PrepareViewModelListener as ShardPrepareViewModelListener;

/**
 * @author  Josh Stuart <josh.stuart@zoopcommerce.com>
 */
class PrepareViewModelListener extends ShardPrepareViewModelListener
{
    public function prepareViewModel(MvcEvent $event, $action)
    {
        if ($event->getTarget()->forward()->getNumNestedForwards() > 0) {
            return $event->getResult();
        }

        $result = $event->getResult();

        $response = $event->getResponse();
        $response->setStatusCode($result->getStatusCode());
        $response->getHeaders()->addHeaders($result->getHeaders());

        $controller = $event->getTarget();

        $viewModel = $controller->acceptableViewModelSelector($controller->getOptions()->getAcceptCriteria());

        //set the template
        if ($viewModel instanceof JsonModel && count($viewModel->getVariables()) == 0) {
            if ($response->getStatusCode() == 200) {
                $response->setStatusCode(204);
            }
            return $response;
        } elseif ($viewModel->getTemplate() == null) {
            $viewModel->setTemplate($controller->getOptions()->getTemplates()[$action]);
        }

        $event->setResult($viewModel);

        return $viewModel;
    }
}
