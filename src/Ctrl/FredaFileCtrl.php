<?php

namespace Lack\Freda\Ctrl;

use Brace\Router\RoutableCtrl;
use Brace\Router\Router;
use Brace\Router\Type\RouteParams;
use Lack\Freda\FredaConfig;
use Lack\Freda\Type\T_FredaFile;
use Lack\Freda\Type\T_FredaMultiGetRequest;
use Laminas\Diactoros\ServerRequest;
use Phore\FileSystem\PhoreUri;

class FredaFileCtrl implements RoutableCtrl
{

    public static function Routes(Router $router, string $mount, array $mw): void
    {
        $router->on("GET@$mount/data/:alias/::file", [self::class, "readFile"], $mw, "freda-set-data");

        $router->on("POST@$mount/data", [self::class, "getMulti"], $mw, "freda-get-data");
        $router->on("POST@$mount/data/:alias/::file", [self::class, "writeFile"], $mw, "freda-get-data");
    }


    protected function parseContent(PhoreUri $uri, string $data) {
        switch ($uri->getExtension()) {
            case "yml":
            case "yaml":
                $data = phore_yaml_decode($data);
                break;
            case "json":
                $data = phore_json_decode($data);
                break;
        }
        return $data;
    }

    public function readFile(RouteParams $routeParams, FredaConfig $fredaConfig, ServerRequest $request) {
        $file = phore_uri($routeParams->get("file"));
        $alias = $routeParams->get("alias");
        $fs = $fredaConfig->getFileSystem($alias);

        $content = $fs->getFile($file);


        $content = $this->parseContent($file, $content);

        if (is_string($content)) {
            return new T_FredaFile(
                alias: $alias, filename: (string)$file, text: $content
            );
        }
        return new T_FredaFile(
            alias: $alias, filename: (string)$file, data: $content
        );
    }


    public function getMulti(T_FredaMultiGetRequest $body, FredaConfig $fredaConfig) {
        $fs = $fredaConfig->getFileSystem($body->alias);
        $ret = [];

        if ($body->globPattern !== null)
            $body->filenames = $fs->glob($body->globPattern);

        foreach ($body->filenames as $file) {
            $content = $fs->getFile($file);
            $content = $this->parseContent(phore_uri($file), $content);
            $ret[] = new T_FredaFile($body->alias, $file, $content);
        }
        return $ret;
    }

    public function writeFile(RouteParams $routeParams, T_FredaFile $body, FredaConfig $fredaConfig) {
        $file = phore_uri($routeParams->get("file"));
        $alias = $routeParams->get("alias");

        $fs = $fredaConfig->getFileSystem($alias);

        switch ($file->getExtension()) {
            case "yml":
            case "yaml":
                $data = phore_yaml_encode($body->data);
                break;
            case "json":
                $data =  phore_json_encode($body->data, prettyPrint: true);
                break;
            default:
        }

        if ($body->text !== null)
            $data = $body->text;
        $fs->setFile($file, $data);

        return ["ok" => "file saved"];
    }
}
