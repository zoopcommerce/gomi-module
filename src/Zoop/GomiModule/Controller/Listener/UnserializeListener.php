<?php

namespace Zoop\GomiModule\Controller\Listener;

use Doctrine\ODM\MongoDB\DocumentManager;
use Zend\Math\Rand;
use Zend\Mvc\MvcEvent;
use Zend\ServiceManager\ServiceManager;
use Zoop\GomiModule\Exception;
use Zoop\Shard\Serializer\Unserializer;
use Zoop\ShardModule\Controller\Result;
use Zoop\ShardModule\Controller\Listener\UnserializeListener as ShardUnserializeListener;
use Zoop\ShardModule\Options\RestfulControllerOptions;

/**
 * @author  Josh Stuart <josh.stuart@zoopcommerce.com>
 */
class UnserializeListener extends ShardUnserializeListener
{
    protected $dm;
    protected $sm;
    protected $options;
    protected $modelClass;
    protected $userClass;
    protected $manifestName;
    protected $config;

    protected function init(MvcEvent $event)
    {
        $options = $event->getTarget()->getOptions();
        $this->setOptions($options);

        $dm = $options->getModelManager();
        $this->setDm($dm);

        $sm = $options->getServiceLocator();
        $this->setSm($sm);

        $modelClass = $options->getClass();
        $this->setModelClass($modelClass);

        $manifestName = $options->getManifest()->getName();
        $this->setManifestName($manifestName);
    }

    public function create(MvcEvent $event)
    {
        $this->init($event);
        $data = $event->getParam('data');

        $criteria = $this->getCriteria($data);

        $userRepository = $this->getDm()
            ->getRepository($this->getUserClass());

        $user = $userRepository->findOneBy($criteria);
        if (!isset($user)) {
            throw new Exception\DocumentNotFoundException();
        }

        $code = $this->createUniqueCode();
        $expiry = $this->getExpiry();

        //clear existing
        $this->deleteExistingToken($user);

        $event->setParam('data', array(
            'code' => $code,
            'username' => $user->getUsername(),
            'expires' => $expiry
        ));

        $result = new Result(
            $event->getTarget()
                ->getOptions()
                ->getManifest()
                ->getServiceManager()
                ->get('unserializer')
                ->fromArray(
                    $event->getParam('data'),
                    $this->getOptions()->getClass(),
                    null,
                    Unserializer::UNSERIALIZE_PATCH
                )
        );
        $event->setResult($result);

        return $result;
    }

    protected function getCriteria($data = array())
    {
        $criteria = [];

        if (isset($data['username']) && !$data['username'] == '') {
            $criteria['username'] = $data['username'];
        }

        if (isset($data['email']) && $data['email'] != '') {
            $metadata = $this->getDm()
                ->getClassMetadata($this->getUserClass());

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

    protected function createUniqueCode()
    {
        return 'c' . substr(bin2hex(Rand::getBytes(30)), 0, 49);
    }

    protected function deleteExistingToken($user)
    {
        $this->getDm()
            ->createQueryBuilder($this->getUserClass())
            ->remove()
            ->field('username')->equals($user->getUsername())
            ->getQuery()
            ->execute();
    }

    /**
     * @return DocumentManager
     */
    public function getDm()
    {
        return $this->dm;
    }

    /**
     * @param DocumentManager $dm
     */
    public function setDm(DocumentManager $dm)
    {
        $this->dm = $dm;
    }

    /**
     * @return RestfulControllerOptions
     */
    public function getOptions()
    {
        return $this->options;
    }

    /**
     * @param RestfulControllerOptions $options
     */
    public function setOptions(RestfulControllerOptions $options)
    {
        $this->options = $options;
    }

    /**
     * @param ServiceManager $sm
     */
    public function setSm(ServiceManager $sm)
    {
        $this->sm = $sm;
    }

    /**
     *
     * @return ServiceManager
     */
    public function getSm()
    {
        return $this->sm;
    }

    /**
     * @return string
     */
    public function getModelClass()
    {
        return $this->modelClass;
    }

    /**
     * @param string $modelClass
     */
    public function setModelClass($modelClass)
    {
        $this->modelClass = $modelClass;
    }

    /**
     * @return string
     */
    public function getManifestName()
    {
        return $this->manifestName;
    }

    /**
     * @param string $manifestName
     */
    public function setManifestName($manifestName)
    {
        $this->manifestName = $manifestName;
    }

    /**
     * @return string
     */
    public function getUserClass()
    {
        return $this->getConfig()['user_class'];
    }

    /**
     * @return string
     */
    public function getExpiry()
    {
        return $this->getConfig()['expiry'];
    }

    /**
     * @return array
     */
    public function getConfig()
    {
        if(!isset($this->config)) {
            $this->config = $this->getSm()->get('config')['zoop']['gomi']['recover_password_token_controller_options'];
        }
        return $this->config;
    }
}
