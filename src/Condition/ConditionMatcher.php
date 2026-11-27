<?php

declare(strict_types=1);

/**
 * Contao Form Conditional Notifications Bundle for Contao Open Source CMS
 * @author     Web Ex Machina
 * @see        https://github.com/Web-Ex-Machina/contao-form-conditional-notifications
 */

namespace WEM\FormConditionalNotificationsBundle\Condition;

use Contao\CoreBundle\Framework\ContaoFramework;
use Contao\FormFieldModel;
use Contao\StringUtil;
use WEM\FormConditionalNotificationsBundle\Model\Field;
use WEM\FormConditionalNotificationsBundle\Model\Notification;

/**
 * Checks whether submitted form data satisfies every condition of a conditional notification.
 */
class ConditionMatcher
{
    public const string OPTION_PREFIX = 'option';

    public const string GROUP_PREFIX = 'group';

    public const string SEPARATOR = '**';

    private const array OPTION_FIELD_TYPES = ['select', 'radio', 'checkbox'];

    public function __construct(
        private readonly ContaoFramework $framework,
    ) {
    }

    /**
     * A notification without any condition is considered as the default one and always matches.
     *
     * @param array<string, mixed> $submittedData
     */
    public function matches(Notification $notification, array $submittedData): bool
    {
        $conditions = $this->framework->getAdapter(Field::class)->findBy('pid', $notification->id, [
            'order' => 'sorting ASC',
        ]);

        if ($conditions === null) {
            return true;
        }

        $formFieldAdapter = $this->framework->getAdapter(FormFieldModel::class);

        foreach ($conditions as $condition) {
            $formField = $formFieldAdapter->findById($condition->field);

            if ($formField === null) {
                continue;
            }

            $submittedValue = $submittedData[$formField->name] ?? null;

            $isMatching = match (true) {
                \in_array($formField->type, self::OPTION_FIELD_TYPES, true) => $this->matchesOptions($formField, StringUtil::deserialize($condition->value, true), $submittedValue),
                // Country codes may be stored and submitted with a different case
                $formField->type === 'countryselect' => strcasecmp((string) $condition->value, $this->stringify($submittedValue)) === 0,
                default => (string) $condition->value === $this->stringify($submittedValue),
            };

            if (! $isMatching) {
                return false;
            }
        }

        return true;
    }

    /**
     * Split a stored condition value ("option**foo" or "group**bar") into its type and value.
     *
     * @return array{0: string, 1: string}
     */
    public static function parseValue(string $value): array
    {
        if (! str_contains($value, self::SEPARATOR)) {
            return [self::OPTION_PREFIX, $value];
        }

        [$type, $optionValue] = explode(self::SEPARATOR, $value, 2);

        return [$type, $optionValue];
    }

    /**
     * @param array<mixed> $conditionValues
     */
    private function matchesOptions(FormFieldModel $formField, array $conditionValues, mixed $submittedValue): bool
    {
        $allowedValues = [];

        foreach ($conditionValues as $conditionValue) {
            [$type, $value] = self::parseValue((string) $conditionValue);

            if ($type === self::GROUP_PREFIX) {
                array_push($allowedValues, ...$this->getGroupOptionValues($formField, $value));
            } else {
                $allowedValues[] = $value;
            }
        }

        if ($allowedValues === []) {
            return true;
        }

        $submittedValues = array_map(strval(...), (array) $submittedValue);

        return array_intersect($submittedValues, $allowedValues) !== [];
    }

    /**
     * Return the values of the options listed under the given option group.
     *
     * @return list<string>
     */
    private function getGroupOptionValues(FormFieldModel $formField, string $group): array
    {
        $values = [];
        $isInGroup = false;

        foreach (StringUtil::deserialize($formField->options, true) as $option) {
            if (! empty($option['group'])) {
                if ($isInGroup) {
                    break;
                }

                $isInGroup = ($option['value'] ?? '') === $group;

                continue;
            }

            if ($isInGroup) {
                $values[] = (string) ($option['value'] ?? '');
            }
        }

        return $values;
    }

    private function stringify(mixed $value): string
    {
        if (\is_array($value)) {
            return implode(',', array_map(strval(...), $value));
        }

        return (string) $value;
    }
}
