<?php

namespace Nagasari\Http;

class RequestFile 
{
    public string $originalPath;
    public string $path;
    public string $size;
    public string $type;
    public string $errorCode;

    public function __construct(string $originalPath, string $path, string $type, string $size, string $errorCode)
    {
        $this->originalPath = $originalPath;
        $this->path = $path;
        $this->size = $size;
        $this->type = $type;
        $this->errorCode = $errorCode;
    }

    public function move(string $path, array $options = []): bool
    {
        $newPath = str_replace([
            '{name}',
            '{extension}',
            '{year}',
            '{month}',
            '{day}',
            '{random}'
        ], [
            pathinfo($path, PATHINFO_BASENAME),
            pathinfo($path, PATHINFO_EXTENSION),
            date('Y'),
            date('m'),
            date('d'),
            bin2hex(random_bytes(8))
        ], $path);

        if (!mkdir(pathinfo($newPath, PATHINFO_DIRNAME), 755, true)) {
            return false;
        }

        if (!move_uploaded_file($this->path, $newPath)) {
            return false;
        }

        $this->path = $newPath;
        return true;
    }
}