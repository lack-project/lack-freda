<?php

namespace Lack\Freda\Ctrl;

use Brace\Router\RoutableCtrl;
use Brace\Router\Router;
use Brace\Router\Type\RouteParams;
use Lack\Freda\FredaConfig;
use Lack\Freda\Type\T_FredaCopyRequest;
use Lack\Freda\Type\T_FredaFTree;
use Laminas\Diactoros\ServerRequest;

class FredaActionCopyCtrl implements RoutableCtrl
{


    public static function Routes(Router $router, string $mount, array $mw): void
    {
        $router->on("POST@$mount/action/copy", [self::class, "copy"]);
    }


    public function copy(RouteParams $routeParams, ServerRequest $request, FredaConfig $fredaConfig, T_FredaCopyRequest $body)
    {


        $srcFs = $fredaConfig->getFileSystem($body->srcAlias);
        $destFs = $fredaConfig->getFileSystem($body->destAlias);

        $copy = function (T_FredaFTree $src) use (&$copy, $srcFs, $destFs, $body) {
            if ($src->children === null) {
                $destFileName = $body->destPath . "/" . $src->relPath;
                if ($destFs->isExisting($destFileName) && ! $body->allowOverwrite)
                    throw new \InvalidArgumentException("File '$destFileName' already existing.");
                $destFs->setFile(
                    $destFileName,
                    $srcFs->getFile($src->fullPath)
                );
                return;
            }
            foreach ($src->children as $child) {
                $copy($child);

            }
        };

        $tree = $srcFs->getTree($body->srcPath, true);
        $copy($tree);

        return ["ok" => "Files copied"];

    }

}