<?php

namespace App\Action;

/**
 * Home Class
 */
class Home extends BaseAction
{
    /**
     * @return mixed
     */
    public function __invoke()
    {
        return ['message' => 'Hello!'];
    }
}
