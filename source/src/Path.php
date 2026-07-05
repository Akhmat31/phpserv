<?php
namespace Source;

final class Path
{
    private string $path;

    public function set(string $path): void
    {
        $this->path = $path;
    }

    public function get(): string
    {
        return $this->path;
    }
}
