<?php

namespace Lack\Freda\Ctrl;

use Brace\Router\Router;
use Brace\Router\Type\RouteParams;
use Lack\Freda\FredaConfig;
use Lack\Freda\Type\T_FredaFile;

class FredaFileCtrl
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
            case "json":
                $content = phore_json_decode($content);
        }

        return new T_FredaFile(
            alias: $alias, filename: (string)$file, data: $content
        );
    }


    public function writeFile(RouteParams $routeParams, T_FredaFile $body) {
        $file = phore_uri($routeParams->get("file"));
        $alias = $routeParams->get("alias");

        $file = $file->withSubPath($routeParams->get("file"))->asFile();

        switch ($file->getExtension()) {
            case "yml":
            case "yaml":
                return $file->set_yaml(phore_json_decode($body));
            case "json":
                return $file->set_json(phore_json_decode($body), prettyPrint: true);
            default:
                return $file->set_contents($body);
        }
    }
}