<?php

namespace Lack\Freda\Ctrl;

use Brace\Core\BraceApp;
use Brace\Router\RoutableCtrl;
use Brace\Router\Router;
use Brace\Router\Type\RouteParams;
use Lack\Freda\FredaConfig;
use Laminas\Diactoros\ServerRequest;

class FredaFormatCtrl implements RoutableCtrl
{


    public static function Routes(Router $router, string $mount, array $mw): void
    {
        $router->on("POST@$mount/format", [self::class, "format"], $mw, "freda-format-data");
    }


    public function format(FredaConfig $fredaConfig, ServerRequest $request, array $body) {
        switch ($request->getQueryParams()["format"]) {
            case "json_pretty":
                $data = phore_json_encode($body["input"], prettyPrint: true);
                break;
            default:
                throw new \InvalidArgumentException("Unknown format");
        } ;
        return ["data" => $data];
    }

}
