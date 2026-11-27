<?php

declare(strict_types=1);

/**
 * Contao Form Conditional Notifications Bundle for Contao Open Source CMS
 * @author     Web Ex Machina
 * @see        https://github.com/Web-Ex-Machina/contao-form-conditional-notifications
 */

namespace WEM\FormConditionalNotificationsBundle\ContaoManager;

use Contao\CoreBundle\ContaoCoreBundle;
use Contao\ManagerPlugin\Bundle\BundlePluginInterface;
use Contao\ManagerPlugin\Bundle\Config\BundleConfig;
use Contao\ManagerPlugin\Bundle\Parser\ParserInterface;
use Terminal42\DcawizardBundle\Terminal42DcawizardBundle;
use Terminal42\NotificationCenterBundle\Terminal42NotificationCenterBundle;
use WEM\FormConditionalNotificationsBundle\WEMFormConditionalNotificationsBundle;

class Plugin implements BundlePluginInterface
{
    public function getBundles(ParserInterface $parser): array
    {
        return [
            BundleConfig::create(WEMFormConditionalNotificationsBundle::class)
                ->setLoadAfter([
                    ContaoCoreBundle::class,
                    Terminal42NotificationCenterBundle::class,
                    Terminal42DcawizardBundle::class,
                ])
                ->setReplace(['wem-fcn']),
        ];
    }
}
