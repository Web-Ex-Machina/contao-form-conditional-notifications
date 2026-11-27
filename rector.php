<?php

declare(strict_types=1);

use Contao\Rector\Set\ContaoSetList;
use Rector\Config\RectorConfig;
use Rector\Set\ValueObject\SetList;

return RectorConfig::configure()
    ->withPaths([
        __DIR__ . '/src',
        __DIR__ . '/contao',
    ])
    ->withPhpSets()
    ->withComposerBased(symfony: true)
    ->withSets([
        SetList::CODING_STYLE,
        SetList::CODE_QUALITY,
        SetList::DEAD_CODE,
        SetList::TYPE_DECLARATION,
        // ContaoLevelSetList::UP_TO_CONTAO_57 relies on set constants removed from recent Rector versions,
        // Symfony rules are loaded through withComposerBased() instead
        ContaoSetList::CONTAO_50,
        ContaoSetList::CONTAO_51,
        ContaoSetList::CONTAO_53,
        ContaoSetList::FQCN,
    ]);
