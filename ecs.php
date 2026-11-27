<?php

declare(strict_types=1);

use PhpCsFixer\Fixer\Comment\HeaderCommentFixer;
use Symplify\CodingStandard\Fixer\ArrayNotation\ArrayOpenerAndCloserNewlineFixer;
use Symplify\EasyCodingStandard\Config\ECSConfig;

return ECSConfig::configure()
    ->withConfiguredRule(HeaderCommentFixer::class, [
        'header' =>
"Contao Form Conditional Notifications Bundle for Contao Open Source CMS
@author     Web Ex Machina

@see        https://github.com/Web-Ex-Machina/contao-form-conditional-notifications"])
    ->withPaths([
        __DIR__ . '/src',
        __DIR__ . '/contao',
    ])
    ->withSkip([
        ArrayOpenerAndCloserNewlineFixer::class,
        __DIR__ . '/vendor',
    ])
    ->withPreparedSets(
        psr12: true,
        common: true,
    );
