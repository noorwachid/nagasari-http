<?php

namespace Nagasari\Http;

class View extends Response
{
    private string $basePath;

    private array $stack;
    private array $map;

    public function __Construct(string $path, array $data = [])
    {
        $this->stack = [];
        $this->map = [];

        extract($data);
        
        ob_start();

        require $path.'.html.php';

        if (!empty($this->basePath)) {
            require $this->basePath.'.html.php';
        }
        
        parent::__construct(ob_get_clean());
    }

    private function Use(string $path): void
    {
        $this->basePath = $path;
    }

    private function Begin(string $section): void
    {
        $this->stack[] = $section;
        ob_start();
    }

    private function End(): void
    {
        $this->map[array_pop($this->stack)] = ob_get_clean();
    }

    private function Set(string $section, string $value): void
    {
        $this->map[$section] = $value;
    }

    private function Get(string $section, string $fallbackValue = ''): string 
    {
        return $this->map[$section] ?? $fallbackValue;
    }

    private function Escape(string $rawBuffer): string 
    {
        return htmlentities($rawBuffer);
    }
}