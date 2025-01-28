<?php

declare(strict_types=1);

namespace WEM\FormConditionalNotificationsBundle\EventListener\Backend\DataContainer;

use Contao\DataContainer;
use Terminal42\NotificationCenterBundle\NotificationCenter;
use Terminal42\NotificationCenterBundle\NotificationType\FormGeneratorNotificationType;

class FormConditionalNotificationListener
{
    public function __construct(private readonly NotificationCenter $notificationCenter)
    {
    }

    /**
     * @return array<string>
     */
    public function onNotificationOptionsCallback(DataContainer $dc): array
    {
        return $this->notificationCenter->getNotificationsForNotificationType(FormGeneratorNotificationType::NAME);
    }
}
