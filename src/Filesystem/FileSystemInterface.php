<?php

namespace Lack\Freda\Filesystem;

interface FileSystemInterface
{
    public function getAlias() : string;
    public function getFile(string $filename) : string;
    public function setFile(string $filename, string $content) : void ;
}