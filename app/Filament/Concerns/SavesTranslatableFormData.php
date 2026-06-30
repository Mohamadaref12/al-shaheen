<?php

namespace App\Filament\Concerns;

trait SavesTranslatableFormData
{
    protected array $cachedTranslationFormData = [];

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $this->cachedTranslationFormData = $this->extractTranslationFormData($data);

        return $this->removeTranslationFormData($data);
    }

    protected function afterCreate(): void
    {
        $this->persistTranslationFormData($this->getRecord(), $this->cachedTranslationFormData);
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $this->cachedTranslationFormData = $this->extractTranslationFormData($data);

        return $this->removeTranslationFormData($data);
    }

    protected function afterSave(): void
    {
        if ($this->cachedTranslationFormData !== []) {
            $this->persistTranslationFormData($this->getRecord(), $this->cachedTranslationFormData);
        }
    }

    protected function extractTranslationFormData(array $data): array
    {
        $model = $this->getRecord() ?? app($this->getModel());
        $extracted = [];

        foreach ($model->translatedAttributeNames() as $field) {
            foreach (['en', 'ar'] as $locale) {
                $key = "{$field}_{$locale}";
                if (array_key_exists($key, $data)) {
                    $extracted[$key] = $data[$key];
                }
            }
        }

        return $extracted;
    }

    protected function removeTranslationFormData(array $data): array
    {
        foreach (array_keys($this->extractTranslationFormData($data)) as $key) {
            unset($data[$key]);
        }

        return $data;
    }

    protected function persistTranslationFormData($record, array $data): void
    {
        foreach ($record->translatedAttributeNames() as $field) {
            foreach (['en', 'ar'] as $locale) {
                $key = "{$field}_{$locale}";
                if (array_key_exists($key, $data)) {
                    $record->setAttribute($key, $data[$key]);
                }
            }
        }

        $record->save();
    }
}
