<?php
/**
 * @package    Zoop
 * @license    MIT
 */

namespace Zoop\GomiModule\Service;

use Zoop\GomiModule\Crypt\Email;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

/**
 *
 * @since   1.0
 * @version $Revision$
 * @author  Tim Roediger <superdweebie@gmail.com>
 */
class CryptEmailFactory implements FactoryInterface
{

    /**
     *
     * @param \Zend\ServiceManager\ServiceLocatorInterface $serviceLocator
     * @return object
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $options = $serviceLocator->get('config')['zoop']['gomi']['crypt_email'];
        return new Email($options['key'], $options['salt']);
    }
}
