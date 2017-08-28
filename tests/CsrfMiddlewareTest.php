<?php

namespace Igorart\Csrf\Test;

use Igorart\Csrf\CsrfMiddleware;
use Igorart\Csrf\InvalidCsrfException;
use Igorart\Csrf\NoCsrfException;
use Interop\Http\ServerMiddleware\DelegateInterface;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class CsrfMiddlewareTest extends TestCase
{
    private function makeMiddleware(&$session = [])
    {
        return new CsrfMiddleware($session);
    }

    public function makeRequest(string $method = 'GET', $params = null)
    {
        $request = $this->getMockBuilder(ServerRequestInterface::class)
            ->getMock();
        $request->method('getMethod')->willReturn($method);
        $request->method('getParsedBody')->willReturn($params);

        return $request;
    }

    public function makeDelegate()
    {
        $delegate = $this->getMockBuilder(DelegateInterface::class)
            ->getMock();
        $delegate->method('process')->willReturn($this->makeResponse());

        return $delegate;
    }

    public function makeResponse()
    {
        return $this->getMockBuilder(ResponseInterface::class)->getMock();
    }

    public function testAcceptValidSession()
    {
        $a = [];
        $b = $this->getMockBuilder(\ArrayAccess::class)->getMock();
        $middlewareA = $this->makeMiddleware($a);
        $middlewareB = $this->makeMiddleware($b);
        $this->assertInstanceOf(CsrfMiddleware::class, $middlewareA);
        $this->assertInstanceOf(CsrfMiddleware::class, $middlewareB);
    }

    public function testRejectInvalidSession()
    {
        $this->expectException(\TypeError::class);
        $a = new \stdClass();
        $middlewareA = $this->makeMiddleware($a);
    }

    public function testGetPass()
    {
        $middleware = $this->makeMiddleware();
        $delegate = $this->makeDelegate();
        $delegate->expects($this->once())->method('process');
        $middleware->process(
            $this->makeRequest('GET'),
            $delegate
        );
    }

    public function testPreventPost()
    {
        $middleware = $this->makeMiddleware();
        $delegate = $this->makeDelegate();
        $delegate->expects($this->never())->method('process');
        $this->expectException(NoCsrfException::class);
        $middleware->process(
            $this->makeRequest('POST'),
            $delegate
        );
    }

    public function testPostWithValidToken()
    {
        $middleware = $this->makeMiddleware();
        $token = $middleware->generateToken();
        $delegate = $this->makeDelegate();
        $delegate->expects($this->once())->method('process')->willReturn($this->makeResponse());
        $middleware->process(
            $this->makeRequest('POST', ['_csrf' => $token]),
            $delegate
        );
    }

    public function testPostWithInvalidToken()
    {
        $middleware = $this->makeMiddleware();
        $token = $middleware->generateToken();
        $delegate = $this->makeDelegate();
        $delegate->expects($this->never())->method('process');
        $this->expectException(InvalidCsrfException::class);
        $middleware->process(
            $this->makeRequest('POST', ['_csrf' => 'aze']),
            $delegate
        );
    }

    public function testPostWithDoubleToken()
    {
        $middleware = $this->makeMiddleware();
        $token = $middleware->generateToken();
        $delegate = $this->makeDelegate();
        $delegate->expects($this->once())->method('process')->willReturn($this->makeResponse());
        $middleware->process(
            $this->makeRequest('POST', ['_csrf' => $token]),
            $delegate
        );
        $this->expectException(InvalidCsrfException::class);
        $middleware->process(
            $this->makeRequest('POST', ['_csrf' => $token]),
            $delegate
        );
    }

    public function testLimitTokens()
    {
        $session = [];
        $middleware = $this->makeMiddleware($session);
        for ($i = 0; $i < 100; ++$i) {
            $token = $middleware->generateToken();
        }
        $this->assertCount(50, $session[$middleware->getSessionKey()]);
        $this->assertSame($token, $session[$middleware->getSessionKey()][49]);
    }
}
