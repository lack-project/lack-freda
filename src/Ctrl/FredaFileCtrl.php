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
            default:
                throw new \InvalidArgumentException("No parser for file type '{$uri->getExtension()}'");
        }
        return $data;
    }

    public function readFile(RouteParams $routeParams, FredaConfig $fredaConfig, ServerRequest $request) {
        $asText = (($request->getQueryParams()["asText"] ?? false) == "true");
        $file = phore_uri($routeParams->get("file"));
        $alias = $routeParams->get("alias");
        $fs = $fredaConfig->getFileSystem($alias);

        $content = $fs->getFile($file);


        if ($asText) {
            return new T_FredaFile(
                alias: $alias, filename: (string)$file, text: $content
            );
        }
        $content = $this->parseContent($file, $content);

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
            if ($body->asText) {
                $ret[] = new T_FredaFile($body->alias, $file, text: $content);
                continue;
            }
            $content = $this->parseContent(phore_uri($file), $content);
            $ret[] = new T_FredaFile($body->alias, $file, $content);
        }
        return $ret;
    }

    public function writeFile(RouteParams $routeParams, T_FredaFile $body, FredaConfig $fredaConfig) {
        $file = phore_uri($routeParams->get("file"));
        $alias = $routeParams->get("alias");

        $fs = $fredaConfig->getFileSystem($alias);

        if ($body->data !== null) {
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
        } elseif ($body->text !== null) {
            $data = $body->text;
        } else {
            throw new \InvalidArgumentException("No data or text found.");
        }


        $fs->setFile($file, $data);

        return ["ok" => "file saved"];
    }
}
