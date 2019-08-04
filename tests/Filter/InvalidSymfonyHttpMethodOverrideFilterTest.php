<?php
declare(strict_types=1);

namespace ThreeStreams\Defence\Tests\Filter;

use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use ThreeStreams\Defence\Filter\InvalidSymfonyHttpMethodOverrideFilter;
use ThreeStreams\Defence\Filter\FilterInterface;
use ThreeStreams\Defence\Logger\Logger;
use ThreeStreams\Defence\Envelope;
use ThreeStreams\Defence\Tests\TestsFactory\RequestFactory;
use ReflectionClass;

class InvalidSymfonyHttpMethodOverrideFilterTest extends TestCase
{
    /**
     * See https://github.com/symfony/symfony/blob/4.3/src/Symfony/Component/HttpFoundation/Request.php#L1229
     */
    private function createPostRequestWithMethodOverrideInTheBody(string $overrideMethod): Request
    {
        $postRequest = (new RequestFactory())->createPost();
        $postRequest->enableHttpMethodParameterOverride();
        $postRequest->request->set('_method', $overrideMethod);

        return $postRequest;
    }

    /**
     * See https://github.com/symfony/symfony/blob/4.3/src/Symfony/Component/HttpFoundation/Request.php#L1229
     */
    private function createPostRequestWithMethodOverrideInTheQuery(string $overrideMethod): Request
    {
        $postRequest = (new RequestFactory())->createPost();
        $postRequest->enableHttpMethodParameterOverride();
        $postRequest->query->set('_method', $overrideMethod);

        return $postRequest;
    }

    /**
     * See https://github.com/symfony/symfony/blob/4.3/src/Symfony/Component/HttpFoundation/Request.php#L1227
     */
    private function createPostRequestWithMethodOverrideInAHeader(string $overrideMethod): Request
    {
        $postRequest = (new RequestFactory())->createPost();
        $postRequest->enableHttpMethodParameterOverride();
        $postRequest->headers->set('X-HTTP-METHOD-OVERRIDE', $overrideMethod);

        return $postRequest;
    }

    public function testImplementsFilterinterface()
    {
        $reflectionClass = new ReflectionClass(InvalidSymfonyHttpMethodOverrideFilter::class);

        $this->assertTrue($reflectionClass->implementsInterface(FilterInterface::class));
    }

    /**
     * See https://github.com/symfony/symfony/blob/4.3/src/Symfony/Component/HttpFoundation/Request.php#L1239
     */
    public function providesRequestsWithAnInvalidMethodOverride(): array
    {
        return [[
            true,
            $this->createPostRequestWithMethodOverrideInTheBody('foo'),
        ], [
            true,
            $this->createPostRequestWithMethodOverrideInTheBody('__construct'),
        ], [
            false,
            $this->createPostRequestWithMethodOverrideInTheBody(Request::METHOD_GET),
        ], [
            false,
            $this->createPostRequestWithMethodOverrideInTheBody(Request::METHOD_HEAD),
        ], [
            false,
            $this->createPostRequestWithMethodOverrideInTheBody(Request::METHOD_POST),
        ], [
            false,
            $this->createPostRequestWithMethodOverrideInTheBody(Request::METHOD_PUT),
        ], [
            false,
            $this->createPostRequestWithMethodOverrideInTheBody(Request::METHOD_DELETE),
        ], [
            false,
            $this->createPostRequestWithMethodOverrideInTheBody(Request::METHOD_CONNECT),
        ], [
            false,
            $this->createPostRequestWithMethodOverrideInTheBody(Request::METHOD_OPTIONS),
        ], [
            false,
            $this->createPostRequestWithMethodOverrideInTheBody(Request::METHOD_PATCH),
        ], [
            false,
            $this->createPostRequestWithMethodOverrideInTheBody(Request::METHOD_PURGE),
        ], [
            false,
            $this->createPostRequestWithMethodOverrideInTheBody(Request::METHOD_TRACE),
        ], [
            true,
            $this->createPostRequestWithMethodOverrideInTheQuery('foo'),
        ], [
            true,
            $this->createPostRequestWithMethodOverrideInTheQuery('__construct'),
        ], [
            false,
            $this->createPostRequestWithMethodOverrideInTheQuery(Request::METHOD_GET),
        ], [
            false,
            $this->createPostRequestWithMethodOverrideInTheQuery(Request::METHOD_HEAD),
        ], [
            false,
            $this->createPostRequestWithMethodOverrideInTheQuery(Request::METHOD_POST),
        ], [
            false,
            $this->createPostRequestWithMethodOverrideInTheQuery(Request::METHOD_PUT),
        ], [
            false,
            $this->createPostRequestWithMethodOverrideInTheQuery(Request::METHOD_DELETE),
        ], [
            false,
            $this->createPostRequestWithMethodOverrideInTheQuery(Request::METHOD_CONNECT),
        ], [
            false,
            $this->createPostRequestWithMethodOverrideInTheQuery(Request::METHOD_OPTIONS),
        ], [
            false,
            $this->createPostRequestWithMethodOverrideInTheQuery(Request::METHOD_PATCH),
        ], [
            false,
            $this->createPostRequestWithMethodOverrideInTheQuery(Request::METHOD_PURGE),
        ], [
            false,
            $this->createPostRequestWithMethodOverrideInTheQuery(Request::METHOD_TRACE),
        ], [
            true,
            $this->createPostRequestWithMethodOverrideInAHeader('foo'),
        ], [
            true,
            $this->createPostRequestWithMethodOverrideInAHeader('__construct'),
        ], [
            false,
            $this->createPostRequestWithMethodOverrideInAHeader(Request::METHOD_GET),
        ], [
            false,
            $this->createPostRequestWithMethodOverrideInAHeader(Request::METHOD_HEAD),
        ], [
            false,
            $this->createPostRequestWithMethodOverrideInAHeader(Request::METHOD_POST),
        ], [
            false,
            $this->createPostRequestWithMethodOverrideInAHeader(Request::METHOD_PUT),
        ], [
            false,
            $this->createPostRequestWithMethodOverrideInAHeader(Request::METHOD_DELETE),
        ], [
            false,
            $this->createPostRequestWithMethodOverrideInAHeader(Request::METHOD_CONNECT),
        ], [
            false,
            $this->createPostRequestWithMethodOverrideInAHeader(Request::METHOD_OPTIONS),
        ], [
            false,
            $this->createPostRequestWithMethodOverrideInAHeader(Request::METHOD_PATCH),
        ], [
            false,
            $this->createPostRequestWithMethodOverrideInAHeader(Request::METHOD_PURGE),
        ], [
            false,
            $this->createPostRequestWithMethodOverrideInAHeader(Request::METHOD_TRACE),
        ]];
    }

    /**
     * @dataProvider providesRequestsWithAnInvalidMethodOverride
     */
    public function testInvokeReturnsTrueIfTheMethodOverrideIsInvalid($expected, $request)
    {
        $logger = new Logger();
        $envelope = new Envelope($request, $logger);

        $filter = new InvalidSymfonyHttpMethodOverrideFilter();

        $this->assertSame($expected, $filter($envelope));
    }

    public function testInvokeAddsALogToTheEnvelope()
    {
        $request = $this->createPostRequestWithMethodOverrideInTheQuery('foo');
        $logger = new Logger();

        $envelope = $this
            ->getMockBuilder(Envelope::class)
            ->setConstructorArgs([$request, $logger])
            ->setMethods(['addLog'])
            ->getMock()
        ;

        $envelope
            ->expects($this->once())
            ->method('addLog')
            ->with($this->equalTo('The request contains an invalid Symfony HTTP method override (`FOO`).'))
        ;

        $filter = new InvalidSymfonyHttpMethodOverrideFilter();
        $requestSuspicious = $filter($envelope);

        $this->assertTrue($requestSuspicious);
    }
}
