<?php

namespace Lack\Freda\Ctrl;

use Brace\Router\RoutableCtrl;
use Brace\Router\Router;
use Brace\Router\Type\RouteParams;
use Lack\Freda\FredaConfig;
use Lack\Freda\Type\T_FredaFile;

class FredaFileCtrl implements RoutableCtrl
{

    public static function Routes(Router $router, string $mount, array $mw): void
    {
        $router->on("GET@$mount/data/:alias/::file", [self::class, "readFile"], $mw, "freda-set-data");

        $router->on("POST@$mount/data/:alias/::file", [self::class, "writeFile"], $mw, "freda-get-data");
    }

    public function readFile(RouteParams $routeParams, FredaConfig $fredaConfig) {
        $file = phore_uri($routeParams->get("file"));
        $alias = $routeParams->get("alias");
        $fs = $fredaConfig->getFileSystem($alias);

        $content = $fs->getFile($file);

        switch ($file->getExtension()) {
            case "yml":
            case "yaml":
                $content = phore_yaml_decode($content);
                break;
            case "json":
                $content = phore_json_decode($content);
                break;
        }

        return new T_FredaFile(
            alias: $alias, filename: (string)$file, data: $content
        );
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
                $data = (string)$data;
        }
        $fs->setFile($file, $data);

        return ["ok" => "file saved"];
    }
}