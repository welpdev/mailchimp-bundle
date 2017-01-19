<?php

namespace Welp\MailchimpBundle\Provider;

use Welp\MailchimpBundle\Provider\ProviderInterface;
use Welp\MailchimpBundle\Subscriber\Subscriber;

use FOS\UserBundle\Model\UserManagerInterface;
use FOS\UserBundle\Model\User;

class FosSubscriberProvider implements ProviderInterface
{
    // these tags should match the one you added in MailChimp's backend
    const TAG_USERNAME = 'USERNAME';
    const TAG_ENABLED = 'ENABLED';
    const TAG_LAST_LOGIN = 'LASTLOGIN';

    protected $userManager;

    public function __construct(UserManagerInterface $userManager)
    {
        $this->userManager = $userManager;
    }

    /**
     * {@inheritdoc}
     */
    public function getSubscribers()
    {
        $users = $this->userManager->findUsers();
        // or find only enabled users :
        // $users = $this->userManager->findUserBy(array('enabled' => true));

        $subscribers = array_map(function (User $user) {
            $subscriber = new Subscriber($user->getEmail(), [
                self::TAG_USERNAME => $user->getUsername(),
                self::TAG_ENABLED => $user->isEnabled(),
                self::TAG_LAST_LOGIN => $user->getLastLogin() ? $user->getLastLogin()->format('Y-m-d') : null
            ]);

            return $subscriber;
        }, $users);

        return $subscribers;
    }
}
