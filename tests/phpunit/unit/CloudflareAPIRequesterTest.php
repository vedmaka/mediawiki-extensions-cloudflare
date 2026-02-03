<?php
declare(strict_types=1);

// Lightweight stubs so tests can run without a full MediaWiki runtime.
if ( !class_exists( 'MWException' ) ) {
    class MWException extends \Exception {}
}

if ( !class_exists( 'Config' ) ) {
    class Config {
        private $values = [];
        public function __construct( array $values = [] ) { $this->values = $values; }
        public function get( $k ) { return $this->values[$k] ?? ''; }
    }
}

namespace Psr\Log {
    if ( !interface_exists( 'Psr\\Log\\LoggerInterface' ) ) {
        interface LoggerInterface {
            public function info( $msg );
            public function error( $msg );
        }
    }
}

namespace MediaWiki\Http {
    if ( !class_exists( 'MediaWiki\\Http\\HttpRequestFactory' ) ) {
        class HttpRequestFactory {
            public function createGuzzleClient() {}
        }
    }
}

namespace {
    use PHPUnit\Framework\TestCase;
    use MediaWiki\Extension\Cloudflare\CloudflareAPIRequester;

    // Simple Guzzle-like client stub so we can assert the `post` call.
    if ( !class_exists( 'GuzzleClientStub' ) ) {
        class GuzzleClientStub {
            public $calledWith = null;
            public function post( $endpoint, $args ) {
                $this->calledWith = [ $endpoint, $args ];
                return new class {
                    public function getStatusCode() { return 200; }
                };
            }
        }
    }

    class CloudflareAPIRequesterTest extends TestCase {

        public function testMissingConfigThrowsMWException(): void {
            $config = new \Config( [] );
            $httpFactory = $this->createMock( \MediaWiki\Http\HttpRequestFactory::class );
            $logger = $this->createMock( \Psr\Log\LoggerInterface::class );

            $requester = new CloudflareAPIRequester( $config, $httpFactory, $logger );

            $this->expectException( \MWException::class );
            $requester->cachePurge( [ 'https://example.com/' ] );
        }

        public function testCachePurgeCallsGuzzleAndLogs(): void {
            $zoneId = 'zone-123';
            $config = new \Config( [
                'CloudflareAPIKey' => 'apikey',
                'CloudflareEmail' => 'me@example.com',
                'CloudflareZoneID' => $zoneId,
            ] );

            $guzzle = new \GuzzleClientStub();

            $httpFactory = $this->createMock( \MediaWiki\Http\HttpRequestFactory::class );
            $httpFactory->method( 'createGuzzleClient' )->willReturn( $guzzle );

            $logger = $this->createMock( \Psr\Log\LoggerInterface::class );
            $logger->expects( $this->once() )->method( 'info' )->with( $this->stringContains( 'Purge cache succeeded' ) );

            $requester = new CloudflareAPIRequester( $config, $httpFactory, $logger );

            $urls = [ 'https://example.com/image.png' ];
            $requester->cachePurge( $urls );

            $this->assertNotNull( $guzzle->calledWith, 'Guzzle post was not called' );
            [$endpoint, $args] = $guzzle->calledWith;
            $this->assertStringContainsString( "zones/{$zoneId}/purge_cache", $endpoint );
            $this->assertArrayHasKey( 'headers', $args );
            $this->assertArrayHasKey( 'json', $args );
            $this->assertEquals( [ 'files' => $urls ], $args['json'] );
        }
    }
}
