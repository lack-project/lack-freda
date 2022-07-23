<?php

namespace Lack\Freda\Ctrl;

use Brace\Router\RoutableCtrl;
use Brace\Router\Router;
use Brace\Router\Type\RouteParams;
use Lack\Freda\FredaConfig;
use Laminas\Diactoros\ServerRequest;

class FredaTreeCtrl implements RoutableCtrl
{


    public static function Routes(Router $router, string $mount, array $mw): void
    {
        $router->on("GET@$mount/tree/:alias/::dir", [self::class, "getTree"]);
    }


    public function getTree(RouteParams $routeParams, ServerRequest $request, FredaConfig $fredaConfig)
    {
        $dir = phore_uri($routeParams->get("dir")?? "");
        $alias = $routeParams->get("alias");
        $fs = $fredaConfig->getFileSystem($alias);

        return $fs->getTree($dir, $request->getQueryParams()["recursive"] == true);
    }

}