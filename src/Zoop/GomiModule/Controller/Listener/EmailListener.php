<?php

namespace Zoop\GomiModule\Controller\Listener;

use Zend\Mail\Message;
use Zend\Mvc\MvcEvent;
use Zend\View\Model\JsonModel;
use Zend\View\Model\ViewModel;
use Zoop\GomiModule\Exception;
use Zoop\GomiModule\Controller\Listener\ListenerHelperTrait;

/**
 * @author  Josh Stuart <josh.stuart@zoopcommerce.com>
 */
class EmailListener
{
    use ListenerHelperTrait;
    
    public function __call($name, $args)
    {
        return $this->email($args[0]);
    }

    protected function email(MvcEvent $event)
    {
        $this->initHelpers($event);
        
        $body = $this->createEmailBody($event);
        
        $sent = $this->send($event, $body);
        
        return $event->getResult();
    }
    
    /**
     * Sends the reset email
     * 
     * @param MvcEvent $event
     * @param ViewModel $body
     * @return boolean
     */
    protected function send(MvcEvent $event, ViewModel $body)
    {
        $options = $this->getOptions();
        $user = $this->getUser($event);
        
        if(!isset($user)) {
            throw new Exception\DocumentNotFoundException();
        }
        
        $plainEmail = $this->getPlainTextEmail($user->getEmail());
        
        $subject = $options->getMailSubject();
        $from = $options->getMailFrom();
        $mailTransport = $options->getMailTransport();

        // Send the email
        $mail = new Message();
        $mail->setBody(
                $options
                    ->getEmailRenderer()
                    ->render($body)
            )
            ->setFrom($from)
            ->addTo($plainEmail)
            ->setSubject($subject);

        return $mailTransport->send($mail);
    }
    
    /**
     * Un-encrypt the email field
     * @param string $email
     * @return string
     */
    protected function getPlainTextEmail($email)
    {
        $metadata = $this->getDocumentManager()
            ->getClassMetadata($this->getUserClassName());
        
        $servicePrefix = 'shard.' . $this->getManifestName() . '.';

        $crypt = $metadata->getCrypt();
        $blockCipherServiceName = $crypt['blockCipher']['email']['service'];
        $blockCipherService = $this->getSm()
            ->get($servicePrefix . $blockCipherServiceName);
         
        $key = $this->getSm()
            ->get($servicePrefix . $crypt['blockCipher']['email']['key'])->getKey();
        
        $plainEmail = $blockCipherService->decryptValue($email, $key);
        
        return $plainEmail;
    }
    
    /**
     * Creates an email body
     * @param MvcEvent $event
     * @return ViewModel
     */
    protected function createEmailBody(MvcEvent $event)
    {
        $model = $event->getResult()->getModel();
        $link = sprintf('/rest/%s/%s', $this->getOptions()->getEndpoint(), $model->getCode());
        
        $body = new ViewModel(
            [
                'username' => $model->getUsername(),
                'link' => $link,
                'hours' => $model->getExpires() / (60 * 60) //Convert expiry from seconds to hours
            ]
        );
        
        $body->setTemplate(
            $this->getOptions()
                ->getEmailTemplate()
        );
        
        return $body;
    }
}
