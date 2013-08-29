<?php

namespace Zoop\GomiModule\Test\Controller;

use Zoop\Shard\Crypt\Hash\BasicHashService;
use Zoop\GomiModule\Test\TestAsset\TestData;
use Zend\Http\Header\Accept;
use Zend\Http\Header\ContentType;
use Zend\Http\Request;
use Zend\Test\PHPUnit\Controller\AbstractHttpControllerTestCase;

class RecoverPasswordTokenControllerTest extends AbstractHttpControllerTestCase
{
    protected static $staticDcumentManager;

    protected static $dbDataCreated = false;

    public static function tearDownAfterClass()
    {
        TestData::remove(static::$staticDcumentManager);
    }

    public function setUp()
    {
        $this->setApplicationConfig(
            include __DIR__ . '/../../../../test.application.config.php'
        );

        parent::setUp();

        $this->documentManager = $this->getApplicationServiceLocator()->get('doctrine.odm.documentmanager.default');
        static::$staticDcumentManager = $this->documentManager;

        if (! static::$dbDataCreated) {
            //Create data in the db to query against
            TestData::create($this->documentManager);
            static::$dbDataCreated = true;
        }
    }

    public function testChangePasswordWithEmail()
    {
        //first create the token
        $accept = new Accept;
        $accept->addMediaType('application/json');

        $this->getRequest()
            ->setMethod(Request::METHOD_POST)
            ->setContent('{"email": "toby@awesome.com"}')
            ->getHeaders()->addHeaders([$accept, ContentType::fromString('Content-type: application/json')]);

        $this->dispatch('/rest/recoverpasswordtoken');

        $response = $this->getResponse();
        $result = json_decode($response->getContent(), true);
        $this->assertFalse(isset($result));
        $this->assertResponseStatusCode(201);
        $this->assertFalse($response->getHeaders()->has('Location'));

        //check the email
        $this->assertTrue(file_exists(__DIR__ . '/../../../../email/test_mail.tmp'));

        //second, use the code in the email to change the password
        $text = file_get_contents(__DIR__ . '/../../../../email/test_mail.tmp');
        preg_match('/\/rest\/recoverpasswordtoken\/[a-zA-Z0-9]+/', $text, $match);

        $accept = new Accept;
        $accept->addMediaType('application/json');

        $this->getRequest()
            ->setMethod(Request::METHOD_PUT)
            ->setContent('{"password": "newPassword1"}')
            ->getHeaders()->addHeaders([$accept, ContentType::fromString('Content-type: application/json')]);

        $this->dispatch($match[0]);

        $response = $this->getResponse();
        $result = json_decode($response->getContent(), true);
        $this->assertFalse(isset($result));
        $this->assertResponseStatusCode(204);

        $user = $this->documentManager
            ->getRepository('Zoop\GomiModule\DataModel\User')
            ->findOneBy(['username' => 'toby']);

        $basicHashService = new BasicHashService;
        $this->assertTrue($basicHashService->hashValue('newPassword1', $user->getSalt()) == $user->getPassword());
    }

    public function testChangePasswordWithUsername()
    {
        //first create the token
        $accept = new Accept;
        $accept->addMediaType('application/json');

        $this->getRequest()
            ->setMethod(Request::METHOD_POST)
            ->setContent('{"username": "toby"}')
            ->getHeaders()->addHeaders([$accept, ContentType::fromString('Content-type: application/json')]);

        $this->dispatch('/rest/recoverpasswordtoken');

        $response = $this->getResponse();
        $result = json_decode($response->getContent(), true);
        $this->assertFalse(isset($result));
        $this->assertResponseStatusCode(201);
        $this->assertFalse($response->getHeaders()->has('Location'));

        //check the email
        $this->assertTrue(file_exists(__DIR__ . '/../../../../email/test_mail.tmp'));

        //second, use the code in the email to change the password
        $text = file_get_contents(__DIR__ . '/../../../../email/test_mail.tmp');
        preg_match('/\/rest\/recoverpasswordtoken\/[a-zA-Z0-9]+/', $text, $match);

        $accept = new Accept;
        $accept->addMediaType('application/json');

        $this->getRequest()
            ->setMethod(Request::METHOD_PUT)
            ->setContent('{"password": "newPassword2"}')
            ->getHeaders()->addHeaders([$accept, ContentType::fromString('Content-type: application/json')]);

        $this->dispatch($match[0]);

        $response = $this->getResponse();
        $result = json_decode($response->getContent(), true);
        $this->assertFalse(isset($result));
        $this->assertResponseStatusCode(204);

        $user = $this->documentManager
            ->getRepository('Zoop\GomiModule\DataModel\User')
            ->findOneBy(['username' => 'toby']);

        $basicHashService = new BasicHashService;
        $this->assertTrue($basicHashService->hashValue('newPassword2', $user->getSalt()) == $user->getPassword());
    }

    public function testStartRecoveryTemplate()
    {
        $this->dispatch('/rest/recoverpasswordtoken');

        $response = $this->getResponse();
        $this->assertResponseStatusCode(200);
        $this->assertTemplateName('zoop/gomi/start-recovery');
    }

    public function testNewPasswordTemplate()
    {
        //first create the token
        $accept = new Accept;
        $accept->addMediaType('application/json');

        $this->getRequest()
            ->setMethod(Request::METHOD_POST)
            ->setContent('{"username": "toby"}')
            ->getHeaders()->addHeaders([$accept, ContentType::fromString('Content-type: application/json')]);

        $this->dispatch('/rest/recoverpasswordtoken');

        $response = $this->getResponse();
        $result = json_decode($response->getContent(), true);
        $this->assertFalse(isset($result));
        $this->assertResponseStatusCode(201);
        $this->assertFalse($response->getHeaders()->has('Location'));

        //check the email
        $this->assertTrue(file_exists(__DIR__ . '/../../../../email/test_mail.tmp'));

        //second, use the code in the email to change the password
        $text = file_get_contents(__DIR__ . '/../../../../email/test_mail.tmp');
        preg_match('/\/rest\/recoverpasswordtoken\/[a-zA-Z0-9]+/', $text, $match);

        $this->getRequest()
            ->setMethod('GET')
            ->setContent('')
            ->getHeaders()->clearHeaders();

        //reset status code from last request
        $response->setStatusCode(200);

        $this->dispatch($match[0]);

        $response = $this->getResponse();
        $this->assertResponseStatusCode(200);
        $this->assertTemplateName('zoop/gomi/new-password');
    }
}
