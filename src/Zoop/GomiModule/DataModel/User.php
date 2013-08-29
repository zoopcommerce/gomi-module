<?php
/**
 * @package    Zoop
 * @license    MIT
 */
namespace Zoop\GomiModule\DataModel;

use Zoop\Common\User\PasswordInterface;
use Zoop\Common\User\UserInterface;
use Zoop\Common\User\RoleAwareUserInterface;
use Zoop\Shard\User\DataModel\PasswordTrait;
use Zoop\Shard\User\DataModel\UserTrait;
use Zoop\Shard\User\DataModel\RoleAwareUserTrait;

//Annotation imports
use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;
use Zoop\Shard\Annotation\Annotations as Shard;

/**
 *
 * @license MIT
 * @since   1.0
 * @author  Tim Roediger <superdweebie@gmail.com>
 *
 * @ODM\Document
 * @Shard\Serializer\ClassName
 * @Shard\AccessControl({
 *     @Shard\Permission\Basic(roles="*",                    allow={"read", "create"}                      ),
 *     @Shard\Permission\Basic(roles="owner",                allow="update::*",        deny="update::roles"),
 *     @Shard\Permission\Basic(roles="sys::recoverpassword", allow="update::password"                      ),
 *     @Shard\Permission\Basic(roles="admin",                allow={"delete", "update::*"}                 )
 * })
 *
 */
class User implements
    PasswordInterface,
    UserInterface,
    RoleAwareUserInterface
{

    use PasswordTrait;
    use UserTrait;
    use RoleAwareUserTrait;

    /**
     * @ODM\String
     * @Shard\Validator\Required
     */
    protected $firstname;

    /**
     * @ODM\Field(type="string")
     * @Shard\Validator\Required
     */
    protected $lastname;

    /**
     * @ODM\String
     * @Shard\Serializer\Ignore("ignore_when_serializing")
     * @Shard\Crypt\BlockCipher(
     *     key = "crypt.emailaddress",
     *     salt = "crypt.emailaddress"
     * )
     * @Shard\Validator\Chain({
     *     @Shard\Validator\Required,
     *     @Shard\Validator\Email
     * })
     */
    protected $email;

    public function getFirstname()
    {
        return $this->firstname;
    }

    public function setFirstname($firstname)
    {
        $this->firstname = $firstname;
    }

    public function getLastname()
    {
        return $this->lastname;
    }

    public function setLastname($lastname)
    {
        $this->lastname = $lastname;
    }

    public function getEmail()
    {
        return $this->email;
    }

    public function setEmail($email)
    {
        $this->email = (string) $email;
    }
}
