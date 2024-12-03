<?php

namespace NoisyWinds\Smartmd;

use Illuminate\Config\Repository;
use Parsedown;

class Smartmd
{
    protected Repository $config;
    protected Parsedown $markdown;

    public function __construct(Repository $config)
    {
        $this->config = $config;
        $this->markdown = new Parsedown();
    }
}
