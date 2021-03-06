<?php declare(strict_types=1);

namespace ApiClients\Tests\Middleware\Timer\ResponseBody;

use ApiClients\Middleware\Timer\ResponseBody\Middleware;
use ApiClients\Tools\TestUtilities\TestCase;
use React\EventLoop\Factory;
use RingCentral\Psr7\Request;
use RingCentral\Psr7\Response;
use function Clue\React\Block\await;

final class MiddlewareTest extends TestCase
{
    public function testPost()
    {
        $response = new Response(200, []);
        $middleware = new Middleware();
        $middleware->pre(new Request('GET', 'https://example.com/'), 'abc');
        $middleware->pre(new Request('GET', 'https://example.com/'), 'def');
        $responseObject = await($middleware->post($response, 'abc'), Factory::create());
        self::assertTrue((float)$responseObject->getHeaderLine(Middleware::HEADER) < 1);
        sleep(1);
        $responseObject = await($middleware->post($response, 'def'), Factory::create());
        self::assertTrue((float)$responseObject->getHeaderLine(Middleware::HEADER) > 1);
    }
}
