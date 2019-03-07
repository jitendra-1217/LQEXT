<?php

namespace Jitendra\Lqext;

interface Storage
{
    /**
     * @param string $data
     */
    public function push(string $data);

    /**
     * @return string|null
     */
    public function pop();
}
