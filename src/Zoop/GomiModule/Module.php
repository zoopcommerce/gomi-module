<?php

/**
 * @package    Zoop
 * @license    MIT
 */

namespace Zoop\GomiModule;

use Zend\View\Model\JsonModel;
use Zend\Mvc\MvcEvent;
/**
 *
 * @license MIT
 * @link    http://www.doctrine-project.org/
 * @since   0.1.0
 * @author  Tim Roediger <superdweebie@gmail.com>
 */
class Module
{
    public function getConfig()
    {
        return include __DIR__ . '/../../../config/module.config.php';
    }
    
    public function onBootstrap(MvcEvent $e) 
    { 
        $events = $e->getTarget()->getEventManager(); 
        $events->attach('dispatch.error', array($this, 
'onDispatchError'), 100); 
    }

    public function onDispatchError(MvcEvent $e)
    {
        $error = $e->getError();
        if (!$error) {
            return;
        }

        $request = $e->getRequest();
        $headers = $request->getHeaders();
        if (!$headers->has('Accept')) {
            return;
        }

        $accept = $headers->get('Accept');
        if (!$accept->match('application/json')) {
            return;
        }

        $model = new JsonModel();

        $e->setResult($model);
        $e->stopPropagation();
        return $model;
    }
}
