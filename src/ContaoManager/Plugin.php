<?php

declare(strict_types=1);

namespace WEM\FormConditionalNotificationsBundle\ContaoManager;

use Contao\CoreBundle\ContaoCoreBundle;
use Contao\ManagerPlugin\Bundle\BundlePluginInterface;
use Contao\ManagerPlugin\Bundle\Config\BundleConfig;
use Contao\ManagerPlugin\Bundle\Parser\ParserInterface;
use Contao\NewsBundle\ContaoNewsBundle;
use Symfony\Component\Config\Loader\LoaderResolverInterface;
use Symfony\Component\HttpKernel\KernelInterface;
use WEM\FormConditionalNotificationsBundle\WEMFormConditionalNotificationsBundle;

/**
 * Plugin for the Contao Manager.
 *
 * @author Web ex Machina <https://www.webexmachina.fr>
 */
class Plugin implements BundlePluginInterface
{
    /**
     * {@inheritdoc}
     */
    public function getBundles(ParserInterface $parser)
    {
        return [
            BundleConfig::create(WEMFormConditionalNotificationsBundle::class)
                ->setLoadAfter([
                    ContaoCoreBundle::class,
                    'notification_center'
                ])
                ->setReplace(['wem-fcn']),
        ];
    }
}
