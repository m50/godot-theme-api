<?php

declare(strict_types=1);

namespace GCSS;

use Exception;

class Endpoint
{
    public function __invoke()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo '<p style="font-size: 4rem">This end point is only accessible via POST. <br />';
            echo 'Please visit <a href="https://github.com/m50/godot-theme-api">the github repository</a> to learn more.</p>';
            die();
        }

        $postInput = file_get_contents('php://input');

        $converter = new Converter();
        try {
            $tresText = $converter->execute($postInput);
            header("Content-Type: text/plain");
            echo $tresText;
        } catch (Exception $e) {
            header("Content-Type: text/plain");
            $code = (int)$e->getCode();
            http_response_code($code >= 400 ? $code : 500);
            echo $e->__toString() . PHP_EOL;
        }
    }
}
