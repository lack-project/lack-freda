<?php

namespace Lack\Freda;

use Lack\Freda\Filesystem\FileSystemInterface;
use Lack\Freda\Filesystem\PosixFileSystem;
use Phore\Core\Exception\NotFoundException;

class FredaConfig
{

    const JS_FILES = [
        __DIR__ . "/../src.js/freda.js",
        __DIR__ . "/../src.js/freda-file.js",
        __DIR__ . "/../src.js/freda-tree.js",
    ];

    public function __construct(
        public string $mount
    ){}

    protected $filesystems = [];

    public function addFileSystem(FileSystemInterface $fileSystem) {
        $this->filesystems[$fileSystem->getAlias()] = $fileSystem;
    }


    public function getFileSystem(string $alias) : FileSystemInterface {
        return $this->filesystems[$alias] ?? throw new NotFoundException("Filesystem '$alias' not registered");
    }


    public function getJavaScriptCode() : string {
        $data = "";
        foreach (self::JS_FILES as $file)
            $data .= file_get_contents($file);
        $data = str_replace("%MOUNT%", $this->mount, $data);
        return $data;
    }

}