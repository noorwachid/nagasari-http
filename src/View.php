<?php

namespace Nagasari\Message;

class View extends Response
{
    private string $basePath;

    private array $sectionStack;
    private array $sectionMap;

    public function __construct(string $path, array $data = [])
    {
        $this->sectionStack = [];
        $this->sectionMap = [];

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
        $this->sectionStack[] = $section;
        ob_start();
    }

    private function end(): void
    {
        $this->sectionMap[array_pop($this->sectionStack)] = ob_get_clean();
    }

    private function set(string $section, string $value): void
    {
        $this->sectionMap[$section] = $value;
    }

    private function get(string $section, string $fallbackValue = ''): string 
    {
        return $this->sectionMap[$section] ?? '';
    }

    private function escape(string $rawBuffer): string 
    {
        return htmlentities($rawBuffer);
    }
}