<?php

namespace App\Support;

use Illuminate\Support\Facades\App;

class LocaleContext
{
    /**
     * @template TReturn
     *
     * @param  callable(): TReturn  $callback
     * @return TReturn
     */
    public static function run(string $locale, callable $callback): mixed
    {
        $previous = App::getLocale();

        App::setLocale($locale);

        try {
            return $callback();
        } finally {
            App::setLocale($previous);
        }
    }
}
