<?php
/**
 * @package    Zoop
 * @license    MIT
 */
namespace Zoop\GomiModule\Controller;

use Zoop\Common\Crypt\Hash;
use Zoop\Shard\Crypt\BlockCipherService;
use Zoop\Shard\Crypt\Hash\BasicHashService;
use Zoop\ShardModule\Controller\JsonRestfulController;
use Zoop\GomiModule\DataModel\User;
use Zoop\GomiModule\Exception;
use Zend\Http\Header\Allow;
use Zend\Http\Response;
use Zend\Mail\Message;
use Zend\Math\Rand;
use Zend\View\Model\ViewModel;

/**
 *
 * @since   1.0
 * @version $Revision$
 * @author  Tim Roediger <superdweebie@gmail.com>
 */
class RecoverPasswordTokenController extends JsonRestfulController
{

    /**
     * This will start the password reset process for an user.
     * If the user is found in the db, a new token is created, and
     * that token is sent to the user's email.
     *
     * @param type $data
     * @return type
     * @throws Exception\LoginFailedException
     */
    public function create($data){

        $documentManager = $this->options->getDocumentManager();
        $userMetadata = $documentManager->getClassMetadata($this->options->getUserClass());

        $criteria = [];
        if ( isset($data['username']) && ! $data['username'] == ''){
            $criteria['username'] = $data['username'];
        }

        if ( isset($data['email']) && $data['email'] != ''){

            $metadata = $documentManager->getClassMetadata($this->options->getUserClass());
            $servicePrefix = 'shard.' . $this->options->getManifestName() . '.';

            $blockCipherServiceName = $metadata->crypt['blockCipher']['email']['service'];
            $blockCipherService = $this->serviceLocator->get($servicePrefix . $blockCipherServiceName);

            $key = $this->serviceLocator->get($servicePrefix . $metadata->crypt['blockCipher']['email']['key'])->getKey();

            if (isset($metadata->crypt['blockCipher']['email']['salt'])){
                $salt = $this->serviceLocator->get($servicePrefix . $metadata->crypt['blockCipher']['email']['salt'])->getSalt();
            } else {
                $salt = null;
            }

            $criteria['email'] = $blockCipherService->encryptValue($data['email'], $key, $salt);
        }

        if (count($criteria) == 0){
            throw new Exception\InvalidArgumentException('Either username or email must be provided');
        }

        $userRepository = $documentManager->getRepository($this->options->getUserClass());
        $user = $userRepository->findOneBy($criteria);
        if ( ! isset($user)){
            throw new Exception\DocumentNotFoundException();
        }

        // create unique recovery code
        $code = 'c' . substr(bin2hex(Rand::getBytes(30)), 0, 49);

        $expiry = $this->options->getExpiry();

        // delete any existing tokens for the user
        $documentManager
            ->createQueryBuilder($this->options->getDocumentClass())
            ->remove()
            ->field('username')->equals($user->getUsername())
            ->getQuery()
            ->execute();

        parent::create([
            'code' => $code,
            'username' => $user->getUsername(),
            'expires' => $expiry + time()
        ]);

        //remove the Location header so the token isn't exposed in the response
        $headers = $this->response->getHeaders();
        $headers->removeHeader($headers->get('Location'));

        $link = '/rest/' . $this->options->getEndpoint()->getName() . '/' . $code;

        // Create email body
        $body = new ViewModel([
            'username' => $user->getUsername(),
            'link' => $link,
            'hours' => $expiry / (60 * 60) //Convert expiry from seconds to hours
        ]);
        $body->setTemplate('email/recover-password');

        //decrypt email
        $blockCipherService = $this->options->getServiceLocator()->get($userMetadata->crypt['blockCipher']['email']['service']);
        $key = $this->options->getServiceLocator()->get($userMetadata->crypt['blockCipher']['email']['key'])->getKey();
        $plainEmail = $blockCipherService->decryptValue($user->getEmail(), $key);

        // Send the email
        $mail = new Message();
        $mail->setBody($this->options->getEmailRenderer()->render($body))
            ->setFrom($this->options->getMailFrom())
            ->addTo($plainEmail)
            ->setSubject($this->options->getMailSubject());

        $this->options->getMailTransport()->send($mail);

        $this->response->setStatusCode(201);
        return $this->response;
    }

    /**
     * This completes the password reset process.
     *
     * @param type $code
     * @param type $data
     * @return type
     */
    public function update($id, $data) {

        $documentManager = $this->options->getDocumentManager();
        $token = $documentManager->createQueryBuilder($this->options->getDocumentClass())
            ->field('code')->equals($id)
            ->field('expires')->gt(new \DateTime)
            ->getQuery()
            ->getSingleResult();

        if ( ! isset($token)){
            throw new Exception\DocumentNotFoundException();
        }

        $user = $documentManager->getRepository($this->options->getUserClass())->findOneBy(['username' => $token->getUsername()]);
        $user->setPassword($data['password']);

        //need to temporarily change user for AccessControl to allow update even though there is no authenticated user
        $sysUser = new User;
        $sysUser->addRole('sys::recoverpassword');
        $serviceLocator = $this->options->getServiceLocator();
        $serviceLocator->setService('user', $sysUser);

        $documentManager->remove($token);
        $this->flush();
        $sysUser->removeRole('sys::recoverpassword');

        $this->response->setStatusCode(204);
        return $this->response;
    }

    /**
     * Tokens cannot be listed
     *
     * @return type
     */
    public function getList(){
        $allow = new Allow;
        $allow->allowMethods(Response::POST);
        $this->response->setStatusCode(405);
        $this->response->getHeaders()->addHeader($allow);
        return $this->response;
    }

    /**
     * Tokens cannot be got
     *
     * @param type $id
     * @return type
     */
    public function get($id){
        $allow = new Allow;
        $allow->allowMethods(Response::PUT);
        $this->response->setStatusCode(405);
        $this->response->getHeaders()->addHeader($allow);
        return $this->response;
    }

    /**
     * Tokens cannot be deleted through the API.
     *
     * @param type $id
     */
    public function deleteList() {
        $allow = new Allow;
        $allow->allowMethods(Response::POST);
        $this->response->setStatusCode(405);
        $this->response->getHeaders()->addHeader($allow);
        return $this->response;
    }

    /**
     * Tokens cannot be deleted through the API.
     *
     * @param type $id
     */
    public function delete($id) {
        $allow = new Allow;
        $allow->allowMethods(Response::PUT);
        $this->response->setStatusCode(405);
        $this->response->getHeaders()->addHeader($allow);
        return $this->response;
    }

    /**
     * Tokens cannot be modified through the API.
     *
     * @param type $id
     */
    public function patchList($data) {
        $allow = new Allow;
        $allow->allowMethods(Response::POST);
        $this->response->setStatusCode(405);
        $this->response->getHeaders()->addHeader($allow);
        return $this->response;
    }

    /**
     * Tokens cannot be modified through the API.
     *
     * @param type $id
     */
    public function patch($id, $data) {
        $allow = new Allow;
        $allow->allowMethods(Response::PUT);
        $this->response->setStatusCode(405);
        $this->response->getHeaders()->addHeader($allow);
        return $this->response;
    }
}
