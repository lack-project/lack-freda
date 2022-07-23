<?php

namespace Lack\Freda\Type;

class T_FredaMultiGetRequest
{

    /**
     * @var string
     */
    public string $alias;

    /**
     * @var string[]|null
     */
    public array $filenames;

    /**
     * @var string|null
     */
    public string|null $globPattern = null;

}