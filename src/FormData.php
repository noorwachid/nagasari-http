<?php

namespace Nagasari\Http;

class FormData
{
    public function composePostMethod(): array
    {
        // swap second keys
        $rootNode = [];
        foreach ($_FILES as $key => $value) {
            foreach ($value as $key2 => $value2) {
                $rootNode[$key2][$key] = $value2;
            }
        }

        // swap first and last keys
        self::walk($_POST, $rootNode['name'], $rootNode['tmp_name'], $rootNode['type'], $rootNode['size'], $rootNode['error']);

        // remove unused variable
        unset($rootNode);

        return $_POST;
    }

    public static function composeNotPostMethod(): array
    {
        // TODO: fix if the request is list then index
        //
        // request:
        // indexA[subindexA] = 1
        // indexA[] = 1
        //
        // parsed:
        // [
        //     "indexA" => [
        //         "subindexA" => [
        //             "definitionOfSubindexA" => 1
        //         ],
        //         // The deffinition of indexA is missing
        //     ]
        // ]
        
        $rawBody = file_get_contents('php://input');

        // determine boundary
        $boundary = substr($rawBody, 0, strpos($rawBody, "\r\n"));


        if (empty($boundary)) {
            parse_str($rawBody, $_POST);
            return $_POST;
        }

        // fetch each part
        $parts = array_slice(explode($boundary, $rawBody), 1);

        foreach ($parts as $part) {
            // if this is the last part, break
            if ($part == "--\r\n") break;

            // separate content from headers
            $part = ltrim($part, "\r\n");
            [$rawHeader, $body] = explode("\r\n\r\n", $part, 2);

            $rawHeader = explode("\r\n", $rawHeader);
            $header = [];
            foreach ($rawHeader as $line) {
                [$name, $value] = explode(':', $line);
                $header[strtolower($name)] = ltrim($value, ' ');
            }

            // parse content disposition
            if (isset($header['content-disposition'])) {
                preg_match(
                    '/^(.+); *name="([^"]+)"(; *filename="([^"]+)")?/',
                    $header['content-disposition'],
                    $matches
                );
                [, $type, $key] = $matches;

                // file field
                if (isset($matches[4])) {
                    $originalPath = $matches[4];
                    $path = tempnam(ini_get('upload_tmp_dir'), 'php');

                    $errorCode = file_put_contents($path, $body);

                    self::placeInBody($key, new File(
                        $originalPath, $path, $type, strlen($body), $errorCode
                    ));
                }

                // text field
                else {
                    $value = substr($body, 0, strlen($body) - 2); // \r\n
                    self::placeInBody($key, $value);
                }
            }
        }
        
        return $_POST;
    }

    private static function walk(&$value, &$originalPath, &$path, &$type, &$size, &$errorCode): void
    {
        if (is_array($originalPath)) {
            foreach ($originalPath as $key => &$originalPathChild) {
                self::walk($value[$key], $originalPathChild, $path[$key], $type[$key], $size[$key], $errorCode[$key]);
            }
        } else {
            $value = new File($originalPath, $path, $type, $size, $errorCode);
        }
    }

    private static function placeInBody(string $key, string|File $value)
    {
        $keys =  explode('[', str_replace(']', '', $key));
        $previous = null;
        $current = &$_POST;
        $currentKey = '';

        foreach($keys as $key) {
            $previous = &$current;
            $current = &$current[$key];
            $currentKey = $key;
        }

        if ($currentKey === '') {
            unset($previous['']);
            $previous[] = $value;
        } else {
            $current = $value;
        }
    }
}
