<?php
/**
 * @package    Zoop
 * @license    MIT
 */
namespace Zoop\GomiModule\Options;

use Zoop\ShardModule\Options\RestfulControllerOptions;
use Zend\View\Renderer\PhpRenderer;

/**
 *
 * @since   1.0
 * @version $Revision$
 * @author  Tim Roediger <superdweebie@gmail.com>
 */
class RecoverPasswordTokenControllerOptions extends RestfulControllerOptions
{

    protected $userClass;

    protected $mailTransport;

    protected $mailFrom;

    protected $expiry;

    protected $mailSubject;

    protected $emailRenderer;

    protected $emailTemplate;

    protected $emailSentTemplate;

    protected $startRecoveryTemplate;

    protected $newPasswordTemplate;

    protected $recoveryCompleteTemplate;

    public function getUserClass()
    {
        return $this->userClass;
    }

    public function setUserClass($userClass)
    {
        $this->userClass = (string) $userClass;
    }

    public function getMailTransport()
    {
        if (is_string($this->mailTransport)) {
            $this->mailTransport = $this->serviceLocator->get($this->mailTransport);
        }

        return $this->mailTransport;
    }

    /**
     *
     * @param \Zend\Mail\Transport\TransportInterface | string $mailTransport
     */
    public function setMailTransport($mailTransport)
    {
        $this->mailTransport = $mailTransport;
    }

    public function getMailFrom()
    {
        return $this->mailFrom;
    }

    public function setMailFrom($mailFrom)
    {
        $this->mailFrom = $mailFrom;
    }

    public function getExpiry()
    {
        return $this->expiry;
    }

    public function setExpiry($expiry)
    {
        $this->expiry = (integer) $expiry;
    }

    public function getMailSubject()
    {
        return $this->mailSubject;
    }

    public function setMailSubject($mailSubject)
    {
        $this->mailSubject = (string) $mailSubject;
    }

    public function getEmailRenderer()
    {
        if (! isset($this->emailRenderer)) {
            $this->emailRenderer = new PhpRenderer;
            $this->emailRenderer->setResolver($this->serviceLocator->get('ViewResolver'));
            $this->emailRenderer->setHelperPluginManager($this->serviceLocator->get('ViewHelperManager'));
        }

        return $this->emailRenderer;
    }

    public function setEmailRenderer($emailRenderer)
    {
        $this->emailRenderer = $emailRenderer;
    }

    public function getEmailTemplate()
    {
        return $this->emailTemplate;
    }

    public function setEmailTemplate($emailTemplate)
    {
        $this->emailTemplate = $emailTemplate;
    }

    public function getEmailSentTemplate()
    {
        return $this->emailSentTemplate;
    }

    public function setEmailSentTemplate($emailSentTemplate)
    {
        $this->emailSentTemplate = $emailSentTemplate;
    }

    public function getStartRecoveryTemplate()
    {
        return $this->startRecoveryTemplate;
    }

    public function setStartRecoveryTemplate($startRecoveryTemplate)
    {
        $this->startRecoveryTemplate = $startRecoveryTemplate;
    }

    public function getNewPasswordTemplate()
    {
        return $this->newPasswordTemplate;
    }

    public function setNewPasswordTemplate($newPasswordTemplate)
    {
        $this->newPasswordTemplate = $newPasswordTemplate;
    }

    public function getRecoveryCompleteTemplate()
    {
        return $this->recoveryCompleteTemplate;
    }

    public function setRecoveryCompleteTemplate($recoveryCompleteTemplate)
    {
        $this->recoveryCompleteTemplate = $recoveryCompleteTemplate;
    }
}
