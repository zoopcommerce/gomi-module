<?php
/**
 * @package    Zoop
 * @license    MIT
 */
namespace Zoop\GomiModule\DataModel;

//Annotation imports
use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;
use Zoop\Shard\Annotation\Annotations as Shard;

/**
 *
 * @license MIT
 * @link    http://www.doctrine-project.org/
 * @since   0.1.0
 * @author  Tim Roediger <superdweebie@gmail.com>
 *
 * @ODM\Document
 * @Shard\Serializer\Ignore
 * @Shard\AccessControl({
 *     @Shard\Permission\Basic(roles="*", allow={"create", "read", "delete"})
 * })
 */
class RecoverPasswordToken
{

    /**
     * @ODM\Id(strategy="none")
     */
    protected $code;

    /**
     * @ODM\Index(unique = true)
     * @ODM\String
     */
    protected $username;

    /**
     *
     * @ODM\Timestamp
     */
    protected $expires;

    public function getCode()
    {
        return $this->code;
    }

    public function setCode($code)
    {
        $this->code = $code;
    }

    public function getUsername()
    {
        return $this->username;
    }

    public function setUsername($username)
    {
        $this->username = (string) $username;
    }

    public function getExpires()
    {
        return $this->expires;
    }

    public function setExpires($expires)
    {
        $this->expires = $expires;
    }
}
