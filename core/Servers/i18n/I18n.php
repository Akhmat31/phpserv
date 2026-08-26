<?php

namespace Source\I18n;

use Source\Exception;

/**
 * Class I18n
 *
 * Internationalization (translation) service.
 * Inspired by CodeIgniter 4's Language class.
 *
 * Loads language files from a directory structure:
 *   lang/en/app.php  -> returns ['greeting' => 'Hello']
 *   lang/id/app.php  -> returns ['greeting' => 'Halo']
 *
 * Supports locale fallback, parameter interpolation, and
 * nested dot-notation keys (file.key).
 */
class I18n
{
    private string $defaultLocale = 'en';
    private string $currentLocale;
    private string $fallbackLocale = 'en';
    private string $langPath;
    private array $loaded = [];
    private static ?I18n $instance = null;

    public function __construct(string $langPath)
    {
        $this->langPath = rtrim($langPath, '/\\');
        $this->currentLocale = $this->defaultLocale;
    }

    /**
     * Get the shared singleton instance.
     */
    public static function getInstance(): self
    {
        if (self::$instance === null) {
            $defaultPath = getcwd() . '/resources/lang';
            self::$instance = new self($defaultPath);
        }

        return self::$instance;
    }

    /**
     * Set the shared singleton instance.
     */
    public static function setInstance(self $instance): void
    {
        self::$instance = $instance;
    }

    /**
     * Set the current locale.
     */
    public function setLocale(string $locale): self
    {
        $this->currentLocale = $locale;

        return $this;
    }

    /**
     * Get the current locale.
     */
    public function getLocale(): string
    {
        return $this->currentLocale;
    }

    /**
     * Set the fallback locale used when translations are missing.
     */
    public function setFallback(string $locale): self
    {
        $this->fallbackLocale = $locale;

        return $this;
    }

    /**
     * Get the fallback locale.
     */
    public function getFallback(): string
    {
        return $this->fallbackLocale;
    }

    /**
     * Get the list of supported locales (directories in langPath).
     *
     * @return array<int, string>
     */
    public function getSupportedLocales(): array
    {
        if (!is_dir($this->langPath)) {
            return [$this->defaultLocale];
        }

        $locales = [];
        $dirs = glob($this->langPath . '/*', GLOB_ONLYDIR) ?: [];

        foreach ($dirs as $dir) {
            $name = basename($dir);
            if (preg_match('/^[a-z]{2}(-[A-Z]{2})?$/', $name)) {
                $locales[] = $name;
            }
        }

        return empty($locales) ? [$this->defaultLocale] : $locales;
    }

    /**
     * Translate a key.
     *
     * @param string $key Dot-notation key: "file.line" or just "line"
     * @param array $params Parameters for {placeholder} interpolation
     * @param string|null $locale Override locale for this call
     */
    public function translate(string $key, array $params = [], ?string $locale = null): string
    {
        $locale = $locale ?? $this->currentLocale;

        [$file, $line] = $this->parseKey($key);

        $text = $this->lookup($file, $line, $locale);

        if ($text === null && $locale !== $this->fallbackLocale) {
            $text = $this->lookup($file, $line, $this->fallbackLocale);
        }

        if ($text === null) {
            return $key;
        }

        return $this->interpolate($text, $params);
    }

    /**
     * Alias of translate() for shorter call sites.
     */
    public function get(string $key, array $params = [], ?string $locale = null): string
    {
        return $this->translate($key, $params, $locale);
    }

    /**
     * Load a language file for a given locale.
     */
    private function loadFile(string $file, string $locale): array
    {
        $cacheKey = "{$locale}.{$file}";

        if (isset($this->loaded[$cacheKey])) {
            return $this->loaded[$cacheKey];
        }

        $filePath = "{$this->langPath}/{$locale}/{$file}.php";

        if (!file_exists($filePath)) {
            $this->loaded[$cacheKey] = [];

            return [];
        }

        $data = require $filePath;

        if (!is_array($data)) {
            $this->loaded[$cacheKey] = [];

            return [];
        }

        $this->loaded[$cacheKey] = $data;

        return $data;
    }

    /**
     * Look up a line in a loaded language file.
     */
    private function lookup(string $file, string $line, string $locale): ?string
    {
        $messages = $this->loadFile($file, $locale);

        if (array_key_exists($line, $messages)) {
            return (string) $messages[$line];
        }

        return null;
    }

    /**
     * Parse a dot-notation key into [file, line].
     * If no dot is present, uses 'app' as the default file.
     *
     * @return array{0:string, 1:string}
     */
    private function parseKey(string $key): array
    {
        if (str_contains($key, '.')) {
            $parts = explode('.', $key, 2);

            return [$parts[0], $parts[1]];
        }

        return ['app', $key];
    }

    /**
     * Replace {placeholder} tokens with parameter values.
     */
    private function interpolate(string $text, array $params): string
    {
        foreach ($params as $name => $value) {
            $text = str_replace('{' . $name . '}', (string) $value, $text);
        }

        return $text;
    }
}
