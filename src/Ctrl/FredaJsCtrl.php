<?php

namespace Lack\Freda\Ctrl;

use Brace\Core\BraceApp;
use Brace\Router\RoutableCtrl;
use Brace\Router\Router;
use Lack\Freda\FredaConfig;

class FredaJsCtrl implements RoutableCtrl
{


    public static function Routes(Router $router, string $mount, array $mw): void
    {
        $router->on("GET@$mount/freda.js", [self::class, "getJs"]);
    }

    public function getJs(FredaConfig $fredaConfig, BraceApp $braceApp) {
        return $braceApp->responseFactory->createResponseWithBody(
            $fredaConfig->getJavaScriptCode(),
            200, ["Content-Type" => "application/javascript"]
        );
    }

}