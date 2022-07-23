<?php

namespace Lack\Freda\Ctrl;

use Brace\Core\BraceApp;
use Brace\Router\RoutableCtrl;
use Brace\Router\Router;
use Brace\Router\Type\RouteParams;
use Lack\Freda\FredaConfig;

class FredaRawCtrl implements RoutableCtrl
{

    const MIME_TYPES = [
        "svg" => "image/svg",
        "png" => "image/png",
        "jpg" => "image/jpg",
        "*" => "text/plain"
    ];


    public static function Routes(Router $router, string $mount, array $mw): void
    {
        $router->on("GET@$mount/raw/:alias/::file", [self::class, "rawFile"], $mw, "freda-raw-data");
    }


    public function rawFile(RouteParams $routeParams, FredaConfig $fredaConfig, BraceApp $braceApp) {
        $file = phore_uri($routeParams->get("file"));
        $alias = $routeParams->get("alias");
        $fs = $fredaConfig->getFileSystem($alias);

        $mimeType = self::MIME_TYPES[$file->getExtension()] ?? throw new \InvalidArgumentException("Cannot determine mime-type for extension '{$file->getExtension()}'");

        return new $braceApp->responseFactory->createResponseWithBody(
            $fs->getFile($file), 200, ["Content-Type" => $mimeType]
        );
    }

}