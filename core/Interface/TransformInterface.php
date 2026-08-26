<?php

namespace Source\Interface;

interface TransformInterface
{
    /**
     * Transform data to the target format
     *
     * @param mixed $data The data to transform
     * @param string $format The target format (e.g. 'json', 'xml', 'csv', 'array')
     * @return string|array The transformed data
     */
    public function transform(mixed $data, string $format): string|array;
    /**
     * Reverse-transform a formatted string back to an array
     *
     * @param string $data The formatted string
     * @param string $format The source format
     * @return array The decoded array
     */
    public function reverseTransform(string $data, string $format): array;
    /**
     * Check if a format is supported
     *
     * @param string $format The format to check
     */
    public function supports(string $format): bool;
    /**
     * Get a list of all supported formats
     *
     * @return array<int, string>
     */
    public function getSupportedFormats(): array;
}
