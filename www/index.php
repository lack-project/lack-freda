<?php
namespace App;
use Brace\Body\BodyMiddleware;
use Brace\Core\Base\ExceptionHandlerMiddleware;
use Brace\Core\Base\JsonReturnFormatter;
use Brace\Core\Base\NotFoundMiddleware;
use Brace\Core\BraceApp;
use Brace\Mod\Request\Zend\BraceRequestLaminasModule;
use Brace\Router\RouterDispatchMiddleware;
use Brace\Router\RouterEvalMiddleware;
use Brace\Router\RouterModule;
use Lack\Freda\Filesystem\PosixFileSystem;
use Lack\Freda\FredaModule;

require __DIR__ . "/../vendor/autoload.php";


$app = new BraceApp();

// Use Laminas (ZendFramework) Request Handler
$app->addModule(new BraceRequestLaminasModule());

// Use the Uri-Based Routing
$app->addModule(new RouterModule());

// Add the Freda Module
$app->addModule(
    new FredaModule(
        new PosixFileSystem("/opt/mock", writeable: true, alias: "default")
    )
);

$app->setPipe([
    // We want to access the Body via $body dependency
    new BodyMiddleware(),

    // We want to provide nice json formatted Errors
    new ExceptionHandlerMiddleware(),

    // Lets evaluate the Uri for Routes
    new RouterEvalMiddleware(),


    // Dispatch the Route by the Controllers defined in 20_api_routes.php
    // and format object returns with JSON
    new RouterDispatchMiddleware([
        new JsonReturnFormatter($app)
    ]),

    // Return a 404 error if RouterDispatch couldn't dispatch route
    new NotFoundMiddleware()
]);


$app->router->on("GET@/", function (BraceApp $braceApp) {
    return $braceApp->responseFactory->createResponseWithBody(file_get_contents("demo.html"), 200, ["Content-Type" => "text/html"]);
});

//$app->router->debugGetRoutes();

$app->run();