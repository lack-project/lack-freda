<?php

namespace Lack\Freda\Type;

class T_FredaFile
{
    public function __construct(
        public string $alias,
        public string $filename,
        /**
         * @var array|null
         */
        public array|null $data = null,

        /**
         * @var string|null
         */
        public string|null $text = null,

        /**
         * @var int|null
         */
        public ?int $size = null,
    ){}
}
