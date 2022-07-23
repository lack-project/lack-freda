<?php

namespace Lack\Freda\Filesystem;

use Lack\Freda\Type\T_FredaFTree;

interface FileSystemInterface
{
    public function getAlias() : string;
    public function getFile(string $filename) : string;
    public function setFile(string $filename, string $content) : void ;
    public function getTree(string $dir, $recursive = false) : T_FredaFTree;

}