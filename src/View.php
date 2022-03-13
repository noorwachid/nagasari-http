<?php

namespace Nagasari\Http;

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

        require ViewManager::$path.$path.ViewManager::$extension;

        if (!empty($this->basePath))
            require ViewManager::$path.$this->basePath.ViewManager::$extension;
        
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

    private function render(string $section, string $fallbackValue = ''): string 
    {
        return $this->sectionMap[$section] ?? '';
    }

    private function escape(string $rawBuffer): string 
    {
        return htmlentities($rawBuffer);
    }
}