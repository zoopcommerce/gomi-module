<?php

namespace Zoop\GomiModule\Test\TestAsset;

use Zoop\GomiModule\DataModel\User;

class TestData
{
    public static function create($serviceLocator, $documentManager)
    {
        //Create data in the db to query against
        $documentManager->getConnection()->selectDatabase('gomi-test');

        //craete temp auth user
        $sysUser = new User;
        $sysUser->addRole('admin');
        $serviceLocator->setService('user', $sysUser);
        
        $user = new User;
        $user->setUsername('toby');
        $user->setFirstName('Toby');
        $user->setLastName('Awesome');
        $user->setPassword('password1');
        $user->setEmail('toby@awesome.com');
        $documentManager->persist($user);
        $documentManager->flush();
        $documentManager->clear();
    }

    public static function remove($documentManager)
    {
        //Cleanup db after all tests have run
        $collections = $documentManager->getConnection()->selectDatabase('gomi-test')->listCollections();
        foreach ($collections as $collection) {
            $collection->remove(array(), array('w' => true));
        }
    }
}
