<?php

declare(strict_types=1);

/**
 * Contao Form Conditional Notifications Bundle for Contao Open Source CMS
 * @author     Web Ex Machina
 * @see        https://github.com/Web-Ex-Machina/contao-form-conditional-notifications
 */

namespace WEM\FormConditionalNotificationsBundle\Condition;

use Contao\CoreBundle\Framework\ContaoFramework;
use Contao\CoreBundle\Intl\Countries;
use Contao\FormFieldModel;
use Contao\StringUtil;

/**
 * Renders a stored condition in a human-readable way for the back end.
 */
class ConditionFormatter
{
    public function __construct(
        private readonly ContaoFramework $framework,
        private readonly Countries $countries,
    ) {
    }

    /**
     * @param array<string, mixed> $condition A tl_wem_form_conditional_notification_field row
     *
     * @return array{label: string, value: string}|null
     */
    public function format(array $condition): array|null
    {
        $formField = $this->framework->getAdapter(FormFieldModel::class)->findById((int) ($condition['field'] ?? 0));

        if ($formField === null) {
            return null;
        }

        $rawValue = (string) ($condition['value'] ?? '');

        $value = match ($formField->type) {
            'select', 'checkbox', 'radio' => implode(', ', array_map(
                static fn (mixed $v): string => ConditionMatcher::parseValue((string) $v)[1],
                StringUtil::deserialize($rawValue, true),
            )),
            'countryselect' => $this->countries->getCountries()[strtoupper($rawValue)] ?? $rawValue,
            default => $rawValue,
        };

        return [
            'label' => (string) ($formField->label ?: $formField->name),
            'value' => $value,
        ];
    }
}
