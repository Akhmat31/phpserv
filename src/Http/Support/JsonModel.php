<?php

namespace Source\Http\Support;

/**
 * Class JsonModel
 * Wrapper for JSON data with convenient access methods
 */
class JsonModel
{
    private array $data;

    public function __construct(array $data = [])
    {
        $this->data = $data;
    }

    /**
     * Get value by key
     */
    public function get(string $key, mixed $default = null): mixed
    {
        return ArrayType::get($this->data, $key, $default);
    }

    /**
     * Set value by key
     */
    public function set(string $key, mixed $value): self
    {
        ArrayType::set($this->data, $key, $value);
        return $this;
    }

    /**
     * Check if key exists
     */
    public function has(string $key): bool
    {
        return ArrayType::has($this->data, $key);
    }

    /**
     * Remove key
     */
    public function forget(string $key): self
    {
        ArrayType::forget($this->data, $key);
        return $this;
    }

    /**
     * Get all data
     */
    public function all(): array
    {
        return $this->data;
    }

    /**
     * Get all data (alias)
     */
    public function toArray(): array
    {
        return $this->data;
    }

    /**
     * Convert to JSON string
     */
    public function toJson(int $options = 0): string
    {
        return json_encode($this->data, $options);
    }

    /**
     * Get only specified keys
     */
    public function only(array $keys): array
    {
        return ArrayType::only($this->data, $keys);
    }

    /**
     * Get all except specified keys
     */
    public function except(array $keys): array
    {
        return ArrayType::except($this->data, $keys);
    }

    /**
     * Merge with another array
     */
    public function merge(array $data): self
    {
        $this->data = array_merge($this->data, $data);
        return $this;
    }

    /**
     * Magic getter
     */
    public function __get(string $key): mixed
    {
        return $this->get($key);
    }

    /**
     * Magic setter
     */
    public function __set(string $key, mixed $value): void
    {
        $this->set($key, $value);
    }

    /**
     * Magic isset
     */
    public function __isset(string $key): bool
    {
        return $this->has($key);
    }

    /**
     * Magic unset
     */
    public function __unset(string $key): void
    {
        $this->forget($key);
    }

    /**
     * Convert to string
     */
    public function __toString(): string
    {
        return $this->toJson();
    }
}