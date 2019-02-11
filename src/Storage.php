<?php

namespace Jitendra\Lqext;

interface Storage
{
    /**
     * @param string       $key
     * @param string|array $data
     */
    public function write(string $key, $data);
}
