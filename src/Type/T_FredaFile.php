<?php

namespace Lack\Freda\Type;

class T_FredaFile
{
    public function __construct(
        public string $alias,
        public string $filename,
        /**
         * @var array
         */
        public array $data,
        /**
         * @var int|null
         */
        public ?int $size = null,
    ){}
}