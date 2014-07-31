<?php

namespace Zoop\GomiModule\Controller\Listener;

use Doctrine\ODM\MongoDB\DocumentManager;
use Zend\Mvc\MvcEvent;
use Zend\ServiceManager\ServiceManager;
use Zoop\GomiModule\Exception;
use Zoop\GomiModule\Options\RecoverPasswordTokenControllerOptions;
use Zoop\GomiModule\DataModel\User;

/**
 * @author  Josh Stuart <josh.stuart@zoopcommerce.com>
 */
trait ListenerHelperTrait
{
    protected $options;

    protected function initHelpers(MvcEvent $event)
    {
        $options = $event->getTarget()->getOptions();
        $this->setOptions($options);
    }

    /**
     * @return DocumentManager
     */
    public function getDocumentManager()
    {
        return $this->getOptions()->getModelManager();
    }

    /**
     * @return RecoverPasswordTokenControllerOptions
     */
    public function getOptions()
    {
        return $this->options;
    }

    /**
     * @param RecoverPasswordTokenControllerOptions $options
     */
    public function setOptions(RecoverPasswordTokenControllerOptions $options)
    {
        $this->options = $options;
    }

    /**
     *
     * @return ServiceManager
     */
    public function getSm()
    {
        return $this->getOptions()->getServiceLocator();
    }

    /**
     * @return string
     */
    public function getModelClassName()
    {
        return $this->getOptions()->getClass();
    }

    /**
     * @return string
     */
    public function getManifestName()
    {
        return $this->getOptions()->getManifest()->getName();
    }

    /**
     * @return string
     */
    public function getUserClassName()
    {
        return $this->getOptions()->getUserClass();
    }

    /**
     * @return string
     */
    public function getExpiry()
    {
        return $this->getOptions()->getExpiry();
    }

    /**
     * @param MvcEvent $event
     * @return mixed User
     */
    protected function getUser(MvcEvent $event)
    {
        $data = $event->getParam('data');
        $criteria = $this->getUserCriteria($data);

        $userRepository = $this->getDocumentManager()
            ->getRepository($this->getUserClassName());

        $sysUser = new User;
        $sysUser->addRole('sys::authenticate');
        $serviceLocator = $this->options->getManifest()->getServiceManager();
        $allowOverride = $serviceLocator->getAllowOverride();
        $serviceLocator->setAllowOverride(true);
        $serviceLocator->setService('user', $sysUser);
        
        $user = $userRepository->findOneBy($criteria);

        $sysUser->removeRole('sys::recoverpassword');
        
        if (!isset($user)) {
            $serviceLocator->setAllowOverride($allowOverride);
            throw new Exception\DocumentNotFoundException();
        }

        $serviceLocator->setService('user', $user);
        $serviceLocator->setAllowOverride($allowOverride);
        
        return $user;
    }

    /**
     * Deletes a token from an existing user
     *
     * @param mixed $user
     */
    protected function deleteExistingToken($user)
    {
        $this->getDocumentManager()
            ->createQueryBuilder($this->getModelClassName())
            ->remove()
            ->field('username')->equals($user->getUsername())
            ->getQuery()
            ->execute();
    }

    /**
     * Gets the criteria for selecting a user from the user collection
     *
     * @param array $data
     */
    protected function getUserCriteria(array $data = [])
    {
        $criteria = [];

        if (isset($data['username']) && !$data['username'] == '') {
            $criteria['username'] = $data['username'];
        }

        if (isset($data['email']) && $data['email'] != '') {
            $metadata = $this->getDocumentManager()
                ->getClassMetadata($this->getUserClassName());

            $servicePrefix = 'shard.' . $this->getManifestName() . '.';

            $crypt = $metadata->getCrypt();
            $blockCipherServiceName = $crypt['blockCipher']['email']['service'];
            $blockCipherService = $this->getSm()
                ->get($servicePrefix . $blockCipherServiceName);

            $key = $this->getSm()
                ->get($servicePrefix . $crypt['blockCipher']['email']['key'])->getKey();

            if (isset($crypt['blockCipher']['email']['salt'])) {
                $salt = $this->getSm()
                    ->get($servicePrefix . $crypt['blockCipher']['email']['salt'])->getSalt();
            } else {
                $salt = null;
            }

            $criteria['email'] = $blockCipherService->encryptValue($data['email'], $key, $salt);
        }

        if (count($criteria) == 0) {
            throw new Exception\InvalidArgumentException('Either username or email must be provided');
        }

        return $criteria;
    }
}
