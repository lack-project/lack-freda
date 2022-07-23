<?php

namespace Lack\Freda\Type;

class T_FredaFile
{
    public function __construct(
        public string $alias,
        public string $filename,
        public array $data,
        public ?int $size = null,
    ){}
}