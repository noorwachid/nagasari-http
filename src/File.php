<?php

namespace Nagasari\Http;

class File 
{
    public string $OriginalPath;
    public string $Path;
    public string $Size;
    public string $Type;
    public string $ErrorCode;

    public function __Construct(string $originalPath, string $path, string $type, string $size, string $errorCode)
    {
        $this->OriginalPath = $originalPath;
        $this->Path = $path;
        $this->Size = $size;
        $this->Type = $type;
        $this->ErrorCode = $errorCode;
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

        if (!move_uploaded_file($this->Path, $newPath)) {
            return false;
        }

        $this->Path = $newPath;
        return true;
    }
}