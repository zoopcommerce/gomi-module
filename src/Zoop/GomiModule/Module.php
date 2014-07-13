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

    public function onBootstrap(MvcEvent $event)
    {
        $events = $event->getTarget()->getEventManager();
        $events->attach('dispatch.error', array($this, 'onDispatchError'), 100);
    }

    public function onDispatchError(MvcEvent $event)
    {
        $error = $event->getError();
        if (!$error) {
            return;
        }

        $request = $event->getRequest();
        $headers = $request->getHeaders();
        if (!$headers->has('Accept')) {
            return;
        }

        $accept = $headers->get('Accept');
        if (!$accept->match('application/json')) {
            return;
        }

        $model = new JsonModel();

        $event->setResult($model);
        $event->stopPropagation();
        return $model;
    }
}
