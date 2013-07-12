<?php

namespace Zoop\GomiModule\Crypt;

use Zoop\Common\Crypt\KeyInterface;
use Zoop\Common\Crypt\SaltInterface;

class Email implements KeyInterface, SaltInterface {

    protected $key;

    protected $salt;

    public  function getKey() {
        return $this->key;
    }

    public function getSalt() {
        return $this->salt;
    }

    public function __construct($key, $salt) {
        $this->key = $key;
        $this->salt = $salt;
    }
}