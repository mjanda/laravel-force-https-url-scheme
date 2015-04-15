<?php
namespace Shin1x1\ForceSecureUrlScheme\Test;

use Exception;
use Illuminate\Http\Request;
use Mockery;
use PHPUnit_Framework_TestCase;
use Shin1x1\ForceSecureUrlScheme\ForceSecureUrlScheme;
use Shin1x1\ForceSecureUrlScheme\Test\Utils\Application;

/**
 * Class ForceSecureUrlSchemeTest
 * @package Shin1x1\ForceSecureUrlScheme\Test
 */
class ForceSecureUrlSchemeTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var Application
     */
    protected $app;
    /**
     * @var ForceSecureUrlScheme
     */
    protected $sut;

    /**
     *
     */
    public function setUp()
    {
        parent::setUp();

        $this->app = new Application();
        $this->sut = new ForceSecureUrlScheme($this->app);
        Helpers::$requestUri = '/path';
    }

    /**
     * @test
     * @dataProvider getDataFor_handle_redirect
     */
    public function handle_redirect($headers)
    {
        $request = Mockery::mock(Request::class . '[getClientIp, getRequestUri]', [[], [], [], [], [], $headers]);
        $request->shouldReceive('getClientIp')->andReturn('127.0.0.1');
        $request->shouldReceive('getRequestUri')->andReturn('/path');

        $next = function () {
            throw new Exception();
        };

        $this->sut->handle($request, $next);
    }

    /**
     *
     */
    public function getDataFor_handle_redirect()
    {
        return [
            [[]],
            [['HTTP_X_FORWARDED_PROTO' => 'http', 'REMOTE_ADDR' => '127.0.0.1']],
        ];
    }

    /**
     * @test
     * @dataProvider getDataFor_handle_no_redirect
     * @param array $headers
     */
    public function handle_no_redirect(array $headers)
    {
        $request = Mockery::mock(Request::class . '[getClientIp, getRequestUri]', [[], [], [], [], [], $headers]);
        $request->shouldReceive('getClientIp')->andReturn('127.0.0.1');
        $request->shouldReceive('getRequestUri')->andThrow(new Exception());

        $next = function () {
            $this->assertTrue(true);
        };

        $this->sut->handle($request, $next);
    }

    /**
     *
     */
    public function getDataFor_handle_no_redirect()
    {
        return [
            [['HTTPS' => 'on']],
            [['HTTP_X_FORWARDED_PROTO' => 'https', 'REMOTE_ADDR' => '127.0.0.1']],
        ];
    }
}