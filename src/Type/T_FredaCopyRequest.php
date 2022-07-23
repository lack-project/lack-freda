<?php

namespace Lack\Freda\Type;

class T_FredaCopyRequest
{

    public string $srcAlias;
    public string $srcPath;

    public string $destAlias;
    public string $destPath;

    public bool $allowOverwrite = false;
}