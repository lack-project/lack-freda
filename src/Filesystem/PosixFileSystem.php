<?php

namespace Lack\Freda\Filesystem;

use Lack\Freda\Type\T_FredaFile;
use Lack\Freda\Type\T_FredaFTree;
use Phore\FileSystem\PhoreDirectory;

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
        phore_dir($this->rootDir)->withSubPath($filename)->asFile()->createPath()->set_contents($content);
    }


    public function getTree(string $dir, $recursive = false, array $relPath = []) : T_FredaFTree
    {
        $workDir = phore_dir(phore_dir($this->rootDir)->withSubPath($dir));
        $ret = new T_FredaFTree($this->alias, $dir, implode("/", $relPath), count($relPath) === 0 ? "" : $workDir->getBasename(), "directory");
        if ($workDir->isDirectory()) {
            $ret->children = [];
            $ret->type = "directory";
        }
        foreach ($workDir->getListSorted() as $cur) {
            if ($cur->isDirectory()) {
                $ret->children[] = $this->getTree(
                    $dir . "/" . $cur->getRelPath(),
                    $recursive,
                    [...$relPath, $cur->getRelPath()]
                );
                continue;
            }
            $ret->children[] = new T_FredaFTree(
                $this->alias,
                $dir . "/" . $cur->getRelPath(),
                implode("/", [...$relPath, $cur->getBasename()]),
                $cur->getBasename(),
                "file"
            );

        }
        return $ret;
    }

    /**
     * @param $pattern
     * @return string[]
     */
    public function glob($pattern) : array {
        if (str_contains($pattern, "..") || str_contains($pattern, "~"))
            throw new \InvalidArgumentException("Invalid pattern.");

        $ret = glob($this->rootDir . "/" . $pattern);
        asort($ret);
        for ($i = 0; $i < count ($ret); $i++) {
            $ret[$i] = substr($ret[$i], strlen($this->rootDir)+1);
        }
        return $ret;
    }


    public function isExisting(string $filename): bool
    {
        return phore_file($this->rootDir)->withSubPath($filename)->exists();
    }

    public function rm(string $uri, bool $recursive = false) {
        $dir = phore_dir($this->rootDir)->withSubPath($uri);
        if ($dir->isFile()) {
            $dir->asFile()->unlink();
            return;
        }
        if ($recursive === false)
            throw new \InvalidArgumentException("Cannot delete a directory if recursive isn't set");
        $dir->asDirectory()->rmDir(true);
    }
}