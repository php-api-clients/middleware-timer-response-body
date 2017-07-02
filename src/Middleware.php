<?php declare(strict_types=1);

namespace ApiClients\Middleware\Timer\ResponseBody;

use ApiClients\Foundation\Middleware\Annotation\SecondLast;
use ApiClients\Foundation\Middleware\MiddlewareInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use React\Promise\CancellablePromiseInterface;
use Throwable;
use function React\Promise\resolve;

final class Middleware implements MiddlewareInterface
{
    const HEADER = 'X-Middleware-Timer-Response-Body';

    /**
     * @var float[]
     */
    private $time = [];

    /**
     * Return the processed $request via a fulfilled promise.
     * When implementing cache or other feature that returns a response, do it with a rejected promise.
     * If neither is possible, e.g. on some kind of failure, resolve the unaltered request.
     *
     * @param  RequestInterface            $request
     * @param  array                       $options
     * @return CancellablePromiseInterface
     *
     * @SecondLast()
     */
    public function pre(
        RequestInterface $request,
        string $transactionId,
        array $options = []
    ): CancellablePromiseInterface {
        $this->time[$transactionId] = microtime(true);

        return resolve($request);
    }

    /**
     * Return the processed $response via a promise.
     *
     * @param  ResponseInterface           $response
     * @param  array                       $options
     * @return CancellablePromiseInterface
     *
     * @SecondLast()
     */
    public function post(
        ResponseInterface $response,
        string $transactionId,
        array $options = []
    ): CancellablePromiseInterface {
        $time = microtime(true) - $this->time[$transactionId];
        unset($this->time[$transactionId]);

        return resolve($response->withAddedHeader(self::HEADER, (string)$time));
    }

    public function error(
        Throwable $throwable,
        string $transactionId,
        array $options = []
    ): CancellablePromiseInterface {
        unset($this->time[$transactionId]);
    }
}
