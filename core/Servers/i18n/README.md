# i18n Subsystem

Provides internationalization (translation) support with locale fallback.

## Classes

- `Source\I18n\I18n` — Translation service with file-based language packs

## Language File Structure

```
resources/lang/
  en/
    app.php    -> return ['greeting' => 'Hello', 'welcome' => 'Welcome'];
    errors.php -> return ['not_found' => 'Not found'];
  id/
    app.php    -> return ['greeting' => 'Halo', 'welcome' => 'Selamat datang'];
```

## Usage

```php
use Source\I18n\I18n;

$i18n = new I18n(__DIR__ . '/resources/lang');
$i18n->setLocale('id');

// Translate
echo $i18n->translate('app.greeting');           // "Halo"
echo $i18n->translate('app.welcome', ['name' => 'Akhmat']); // "Selamat datang"

// Falls back to 'en' if translation missing in 'id'

// Via Features facade
\PhxPlugins\Features::initI18n(__DIR__ . '/resources/lang');
\PhxPlugins\Features::i18n()->translate('app.greeting');
```

## Methods

- `setLocale(string)` / `getLocale()` — Current locale
- `setFallback(string)` / `getFallback()` — Fallback locale
- `getSupportedLocales()` — List of available locales
- `translate(key, params, locale?)` — Translate with `{placeholder}` interpolation
