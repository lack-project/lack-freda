<?php

namespace Lack\Freda;

use Brace\Core\BraceApp;
use Brace\Core\BraceModule;
use Lack\Freda\Ctrl\FredaFileCtrl;
use Lack\Freda\Filesystem\FileSystemInterface;
use Phore\Di\Container\Producer\DiService;

class FredaModule implements BraceModule
{

    public function __construct(
        protected FileSystemInterface|array $filesystems,
        protected string $apiMount = "/api/freda",
        protected array $mw = []
    ){
        if (! is_array($this->filesystems))
            $this->filesystems = [$this->filesystems];
    }


    public function register(BraceApp $app)
    {
        $app->define("fredaConfig", new DiService(function () {
            $fc = new FredaConfig();
            foreach ($this->filesystems as $fs)
                $fc->addFileSystem($fs);
            return $fc;
        }));

        $app->router->registerClass($this->apiMount, FredaFileCtrl::class, $this->mw);
        $app->router->registerClass($this->apiMount, FredaDirCtrl::class, $this->mw);
    }
}