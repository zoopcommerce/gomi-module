<?php
/**
 * @package    Zoop
 * @license    MIT
 */

namespace Zoop\GomiModule\Service;

use Zoop\GomiModule\Controller\RecoverPasswordTokenController;
use Zoop\GomiModule\Options\RecoverPasswordTokenControllerOptions;
use Zoop\Shard\Rest\Endpoint;
use Zoop\ShardModule\Controller\JsonRestfulController\DoctrineSubscriber;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

/**
 *
 * @since   1.0
 * @version $Revision$
 * @author  Tim Roediger <superdweebie@gmail.com>
 */
class RecoverPasswordTokenControllerFactory implements FactoryInterface
{

    /**
     *
     * @param \Zend\ServiceManager\ServiceLocatorInterface $serviceLocator
     * @return object
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $options = $serviceLocator->getServiceLocator()->get('config')['zoop']['gomi']['recover_password_token_controller_options'];
        $options['service_locator'] = $serviceLocator->getServiceLocator()->get('shard.' . $options['manifest_name'] . '.servicemanager');

        $options['endpointMap'] = $options['service_locator']->get('endpointMap');
        $options['endpoint'] = $options['endpointMap']->getEndpoint($options['endpoint']);
        $options['document_class'] = $options['endpoint']->getClass();
        $options['document_manager'] = $serviceLocator->getServiceLocator()->get($options['document_manager']);

        $instance = new RecoverPasswordTokenController(new RecoverPasswordTokenControllerOptions($options));
        $instance->setDoctrineSubscriber(new DoctrineSubscriber);
        return $instance;
    }
}
