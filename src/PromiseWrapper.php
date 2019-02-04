<?php

declare(strict_types=1);

namespace Denpa\Bitcoin;

use GuzzleHttp\Promise\PromiseInterface;
use Throwable;

class PromiseWrapper implements PromiseInterface
{
    /**
     * Guzzle promise.
     *
     * @var \GuzzleHttp\Promise\PromiseInterface
     */
    protected $promise;

    /**
     * Constructs promise wrapper.
     *
     * @param \GuzzleHttp\Promise\PromiseInterface $promise
     *
     * @return void
     */
    public function __construct(PromiseInterface $promise)
    {
        $this->promise = $promise;
    }

    /**
     * Appends fulfillment and rejection handlers to the promise, and returns
     * a new promise resolving to the return value of the called handler.
     *
     * @param callable $onFulfilled Invoked when the promise fulfills.
     * @param callable $onRejected  Invoked when the promise is rejected.
     *
     * @return \GuzzleHttp\Promise\PromiseInterface
     */
    public function then(
        callable $onFulfilled = null,
        callable $onRejected = null
    ) : PromiseInterface {
        return $this->promise->then($onFulfilled, function ($exception) use ($onRejected) {
            try {
                exception()->handle($exception);
            } catch (Throwable $exception) {
                $onRejected($exception);
            }
        });
    }

    /**
     * Appends a rejection handler callback to the promise, and returns a new
     * promise resolving to the return value of the callback if it is called,
     * or to its original fulfillment value if the promise is instead
     * fulfilled.
     *
     * @param callable $onRejected Invoked when the promise is rejected.
     *
     * @return \GuzzleHttp\Promise\PromiseInterface
     */
    public function otherwise(callable $onRejected) : PromiseInterface
    {
        return $this->then(null, $onRejected);
    }

    /**
     * Get the state of the promise ("pending", "rejected", or "fulfilled").
     *
     * The three states can be checked against the constants defined on
     * PromiseInterface: PENDING, FULFILLED, and REJECTED.
     *
     * @return string
     */
    public function getState() : string
    {
        return $this->promise->getState();
    }

    /**
     * Resolve the promise with the given value.
     *
     * @param mixed $value
     *
     * @throws \RuntimeException if the promise is already resolved.
     *
     * @return void
     */
    public function resolve($value) : void
    {
        $this->promise->resolve();
    }

    /**
     * Reject the promise with the given reason.
     *
     * @param mixed $reason
     *
     * @throws \RuntimeException if the promise is already resolved.
     *
     * @return void
     */
    public function reject($reason) : void
    {
        $this->promise->reject($reason);
    }

    /**
     * Cancels the promise if possible.
     *
     * @return void
     *
     * @link https://github.com/promises-aplus/cancellation-spec/issues/7
     */
    public function cancel() : void
    {
        $this->promise->cancel();
    }

    /**
     * Waits until the promise completes if possible.
     *
     * Pass $unwrap as true to unwrap the result of the promise, either
     * returning the resolved value or throwing the rejected exception.
     *
     * If the promise cannot be waited on, then the promise will be rejected.
     *
     * @param bool $unwrap
     *
     * @throws \LogicException if the promise has no wait function or if the
     *                         promise does not settle after waiting.
     *
     * @return mixed
     */
    public function wait($unwrap = true)
    {
        return $this->promise->wait();
    }
}
