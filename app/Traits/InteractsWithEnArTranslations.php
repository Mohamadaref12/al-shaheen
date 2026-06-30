<?php

namespace App\Traits;

trait InteractsWithEnArTranslations
{
    public function getAttribute($key)
    {
        if (preg_match('/^(.+)_(en|ar)$/', (string) $key, $matches)) {
            $field = $matches[1];
            $locale = $matches[2];

            if (method_exists($this, 'translatedAttributeNames') && in_array($field, $this->translatedAttributeNames(), true)) {
                return $this->getTranslatedAttribute($field, $locale);
            }
        }

        return parent::getAttribute($key);
    }

    public function setAttribute($key, $value)
    {
        if (preg_match('/^(.+)_(en|ar)$/', (string) $key, $matches)) {
            $field = $matches[1];
            $locale = $matches[2];

            if (method_exists($this, 'translatedAttributeNames') && in_array($field, $this->translatedAttributeNames(), true)) {
                $this->setTranslatedAttribute($field, $value, $locale);

                return $this;
            }
        }

        return parent::setAttribute($key, $value);
    }

    public function translatedAttributes(): array
    {
        return property_exists($this, 'translatedAttributes')
            ? $this->translatedAttributes
            : [];
    }
}
