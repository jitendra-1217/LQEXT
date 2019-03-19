<?php

namespace Jitendra\Lqext;

/**
 * @see readme.md Unused interface for now.
 */
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
