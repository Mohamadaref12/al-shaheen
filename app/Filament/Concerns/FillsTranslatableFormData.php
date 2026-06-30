<?php

namespace App\Filament\Concerns;

trait FillsTranslatableFormData
{
    protected function mutateFormDataBeforeFill(array $data): array
    {
        $record = $this->getRecord();
        $record->load('translations');

        foreach ($record->translatedAttributeNames() as $field) {
            foreach (['en', 'ar'] as $locale) {
                $data["{$field}_{$locale}"] = $record->translate($locale, false)?->{$field};
            }
        }

        return $data;
    }
}
