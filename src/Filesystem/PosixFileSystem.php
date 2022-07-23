<?php

namespace Lack\Freda\Filesystem;

class PosixFileSystem implements FileSystemInterface
{

    public function __construct(
        public string $rootDir,
        public bool $writeable = false,
        public string $alias = "default",
    ){}


    public function getAlias() : string {
        return $this->alias;
    }

    public function getFile(string $filename) : string {
        return phore_dir($this->rootDir)->withSubPath($filename)->assertFile()->get_contents();
    }

    public function setFile(string $filename, string $content) : void {
        if ( ! $this->writeable)
            throw new \InvalidArgumentException("Freda Filesystem '$this->alias' is not writable.");
        phore_dir($this->rootDir)->withSubPath($filename)->asFile()->set_contents($content);
    }


}