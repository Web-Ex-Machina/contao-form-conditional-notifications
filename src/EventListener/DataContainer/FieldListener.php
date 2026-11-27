<?php

declare(strict_types=1);

/**
 * Contao Form Conditional Notifications Bundle for Contao Open Source CMS
 * @author     Web Ex Machina
 * @see        https://github.com/Web-Ex-Machina/contao-form-conditional-notifications
 */

namespace WEM\FormConditionalNotificationsBundle\EventListener\DataContainer;

use Contao\ContentModel;
use Contao\CoreBundle\DependencyInjection\Attribute\AsCallback;
use Contao\CoreBundle\Framework\ContaoFramework;
use Contao\CoreBundle\Intl\Countries;
use Contao\DataContainer;
use Contao\Form;
use Contao\FormFieldModel;
use Contao\FormModel;
use Contao\System;
use Contao\Widget;
use WEM\FormConditionalNotificationsBundle\Condition\ConditionFormatter;
use WEM\FormConditionalNotificationsBundle\Condition\ConditionMatcher;
use WEM\FormConditionalNotificationsBundle\Model\Notification;

class FieldListener
{
    private const string TABLE = 'tl_wem_form_conditional_notification_field';

    public function __construct(
        private readonly ContaoFramework $framework,
        private readonly ConditionFormatter $conditionFormatter,
        private readonly Countries $countries,
    ) {
    }

    /**
     * Turn the "value" field into a select when the condition targets a field with options.
     */
    #[AsCallback(table: self::TABLE, target: 'config.onload')]
    public function adjustValueField(DataContainer|null $dc = null): void
    {
        if (! $dc?->id || null === $record = $dc->getCurrentRecord()) {
            return;
        }

        $formField = $this->framework->getAdapter(FormFieldModel::class)->findById((int) $record['field']);

        if ($formField === null) {
            return;
        }

        $valueField = &$GLOBALS['TL_DCA'][self::TABLE]['fields']['value'];

        switch ($formField->type) {
            case 'select':
            case 'radio':
            case 'checkbox':
                $valueField['inputType'] = 'select';
                $valueField['options'] = $this->getFormFieldOptions($formField);
                $valueField['eval']['chosen'] = true;
                $valueField['eval']['multiple'] = true;
                break;

            case 'countryselect':
                $valueField['inputType'] = 'select';
                $valueField['options'] = array_change_key_case($this->countries->getCountries());
                $valueField['eval']['chosen'] = true;
                $valueField['eval']['includeBlankOption'] = true;
                break;
        }
    }

    /**
     * @return array<int, string>
     */
    #[AsCallback(table: self::TABLE, target: 'fields.field.options')]
    public function getFormFieldsOptions(DataContainer|null $dc = null): array
    {
        if ($dc?->table !== self::TABLE || null === $record = $dc->getCurrentRecord()) {
            return [];
        }

        $notification = $this->framework->getAdapter(Notification::class)->findById((int) $record['pid']);

        if ($notification === null) {
            return [];
        }

        $formFields = $this->framework->getAdapter(FormFieldModel::class)->findBy('pid', (int) $notification->pid, [
            'order' => 'sorting ASC',
        ]);
        $options = [];

        foreach ($formFields ?? [] as $formField) {
            if (! $formField->name) {
                continue;
            }

            $options[$formField->id] = \sprintf('%s [%s]', $formField->label ?: $formField->name, $formField->name);
        }

        return $options;
    }

    /**
     * @param array<string, mixed> $row
     */
    #[AsCallback(table: self::TABLE, target: 'list.sorting.child_record')]
    public function listCondition(array $row): string
    {
        $formatted = $this->conditionFormatter->format($row);

        return $formatted === null ? '-' : \sprintf('%s | %s', $formatted['label'], $formatted['value']);
    }

    /**
     * Build the options of the form field as they are rendered in the front end, so options
     * added by "loadFormField" hooks are available too.
     *
     * @return array<string, string>
     */
    private function getFormFieldOptions(FormFieldModel $formField): array
    {
        $widgetClass = $GLOBALS['TL_FFL'][$formField->type] ?? null;

        if ($widgetClass === null || ! is_a($widgetClass, Widget::class, true)) {
            return [];
        }

        $widget = new $widgetClass($formField->row());

        if (\is_array($GLOBALS['TL_HOOKS']['loadFormField'] ?? null) && null !== $formModel = $this->framework->getAdapter(FormModel::class)->findById((int) $formField->pid)) {
            $element = new ContentModel();
            $element->form = $formModel->id;
            $form = new Form($element);

            foreach ($GLOBALS['TL_HOOKS']['loadFormField'] as $callback) {
                $widget = System::importStatic($callback[0])->{$callback[1]}($widget, $formModel->formID ? 'auto_' . $formModel->formID : 'auto_form_' . $formModel->id, $formModel->row(), $form);
            }
        }

        $options = [];

        foreach ((array) $widget->options as $option) {
            $isGroup = ! empty($option['group']);
            $value = ($isGroup ? ConditionMatcher::GROUP_PREFIX : ConditionMatcher::OPTION_PREFIX) . ConditionMatcher::SEPARATOR . $option['value'];

            $options[$value] = ($isGroup ? 'GROUP - ' : 'OPTION - ') . $option['label'];
        }

        return $options;
    }
}
