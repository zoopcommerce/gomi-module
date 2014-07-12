<?php

namespace Zoop\GomiModule\Controller\Listener;

use Zend\Http\Header\Location;
use Zend\Mvc\MvcEvent;
use Zoop\ShardModule\Exception;
use Zoop\ShardModule\Controller\Listener\CreateListener as ShardCreateListener;

/**
 *
 * @since   1.0
 * @version $Revision$
 * @author  Tim Roediger <superdweebie@gmail.com>
 */
class CreateListener extends ShardCreateListener
{
    protected function doAction(MvcEvent $event, $metadata, $documentManager)
    {
        $result = $event->getResult();
        $createdDocument = $result->getModel();

        if ($event->getTarget()->forward()->getNumNestedForwards() == 0 &&
            $documentManager->contains($createdDocument)
        ) {
            $exception = new Exception\DocumentAlreadyExistsException;
            $exception->setDocument($createdDocument);
            throw $exception;
        }
        if (! $documentManager->getClassMetadata(get_class($createdDocument))->isEmbeddedDocument) {
            $documentManager->persist($createdDocument);
        }

        $result->setStatusCode(201);

        return $result;
    }
}
