<?php

namespace Lack\Freda\Type;

class T_FredaFTree
{

    public function __construct(
        public string $alias,
        public string $fullPath,
        public string $relPath,
        public string $name,
        public string $type,

        /**
         * @var T_FredaFTree[]
         */
        public array|null $children = null,

    ) {


    }
}