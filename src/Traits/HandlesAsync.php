<?php

declare(strict_types=1);

namespace Denpa\Bitcoin\Traits;

use Exception;
use Psr\Http\Message\ResponseInterface;

trait HandlesAsync
{
    /**
     * Handles async request success.
     *
     * @param \Psr\Http\Message\ResponseInterface $response
     * @param callable|null                       $callback
     *
     * @return void
     */
    protected function onSuccess(ResponseInterface $response, ?callable $callback = null): void
    {
        if (!is_null($callback)) {
            $callback($response);
        }
    }

    /**
     * Handles async request failure.
     *
     * @param \Exception    $exception
     * @param callable|null $callback
     *
     * @return void
     */
    protected function onError(Exception $exception, ?callable $callback = null): void
    {
        if (!is_null($callback)) {
            $callback($exception);
        }
    }
}
