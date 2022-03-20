<?php

namespace Nagasari\Http;

class View extends Response
{
    private string $basePath;

    private array $stack;
    private array $map;

    public function __construct(string $path, array $data = [])
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

    private function use(string $path): void
    {
        $this->basePath = $path;
    }

    private function begin(string $section): void
    {
        $this->stack[] = $section;
        ob_start();
    }

    private function end(): void
    {
        $this->map[array_pop($this->stack)] = ob_get_clean();
    }

    private function set(string $section, string $value): void
    {
        $this->map[$section] = $value;
    }

    private function get(string $section, string $fallbackValue = ''): string 
    {
        return $this->map[$section] ?? $fallbackValue;
    }

    private function escape(string $rawBuffer): string 
    {
        return htmlentities($rawBuffer);
    }
}