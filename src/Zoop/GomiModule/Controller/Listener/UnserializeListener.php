<?php

namespace Zoop\GomiModule\Controller\Listener;

use \DateTime;
use Zend\Math\Rand;
use Zend\Mvc\MvcEvent;
use Zoop\GomiModule\DataModel\User;
use Zoop\GomiModule\Exception;
use Zoop\GomiModule\Controller\Listener\ListenerHelperTrait;
use Zoop\Shard\Serializer\Unserializer;
use Zoop\ShardModule\Controller\Result;
use Zoop\ShardModule\Controller\Listener\UnserializeListener as ShardUnserializeListener;

/**
 * @author  Josh Stuart <josh.stuart@zoopcommerce.com>
 */
class UnserializeListener extends ShardUnserializeListener
{
    use ListenerHelperTrait;

    public function create(MvcEvent $event)
    {
        $this->initHelpers($event);

        $user = $this->getUser($event);

        $code = $this->createUniqueCode();
        $expiry = $this->getExpiry();

        //clear existing
        $this->deleteExistingToken($user);

        $event->setParam('data', [
            'code' => $code,
            'username' => $user->getUsername(),
            'expires' => $expiry
        ]);

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
    
    /**
     * 
     * @param \Zend\Mvc\MvcEvent $event
     * @return type
     */
    public function update(MvcEvent $event)
    {
        $this->initHelpers($event);
        $id = $event->getParam('id');
        
        $token = $this->getToken($id);
        
        $data = $event->getParam('data');
        $data['username'] = $token->getUsername();
        $event->setParam('data', $data);
        
        $user = $this->getUser($event);
        $user->setPassword($data['password']);
        
        $this->flush($token);
        
        //change response to success
        $event->getResponse()->setStatusCode(200);
        
        return new Result();
    }
    
    /**
     * Gets the password reset token model
     * 
     * @param string $id
     * @return mixed
     * @throws Exception\DocumentNotFoundException
     */
    protected function getToken($id)
    {
        $documentManager = $this->getDocumentManager();
        
        $token = $documentManager->createQueryBuilder(
                $this->getOptions()->getClass()
            )
            ->field('code')->equals($id)
            ->field('expires')->gt(new DateTime)
            ->getQuery()
            ->getSingleResult();

        if (! isset($token)) {
            throw new Exception\DocumentNotFoundException();
        }
        
        return $token;
    }
    
    /**
     * Creates a temporary system user with elevated privileges
     * so that the token can be removed and the user password
     * reset, despite having no authenticated user.
     * 
     * @param mixed $token
     */
    protected function flush($token)
    {
        $documentManager = $this->getDocumentManager();
        
        $sysUser = new User;
        $sysUser->addRole('sys::recoverpassword');
        $serviceLocator = $this->options->getServiceLocator();
        $serviceLocator->setService('user', $sysUser);

        $documentManager->remove($token);
        $documentManager->flush();
        
        $sysUser->removeRole('sys::recoverpassword');
    }
    
    /**
     * Create a unique code to reset password with
     * 
     * @return string
     */
    protected function createUniqueCode()
    {
        return 'c' . substr(bin2hex(Rand::getBytes(30)), 0, 49);
    }
}
