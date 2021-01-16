<?php

/**
 * This file is part of the k8s/client library.
 *
 * (c) Chad Sikorra <Chad.Sikorra@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace integration\K8s\Client;

use K8s\Api\Model\Api\Core\v1\Container;
use K8s\Api\Model\Api\Core\v1\Pod;
use K8s\Client\K8s;
use K8s\Client\Options;
use K8s\WsRatchet\RatchetWebsocketAdapter;
use PHPUnit\Framework\TestCase as BaseTestCase;
use Symfony\Component\HttpClient\Psr18Client;
use Symfony\Component\HttpClient\HttpClient;

class TestCase extends BaseTestCase
{
    private const MAX_WAIT_ITERATIONS = 60;

    /**
     * @var RatchetWebsocketAdapter
     */
    protected $websocket;

    /**
     * @var Psr18Client
     */
    protected $httpClient;

    /**
     * @var Options
     */
    protected $options;

    /**
     * @var K8s
     */
    protected $client;

    public function setUp(): void
    {
        parent::setUp();
        $this->httpClient = $http = new Psr18Client(
            HttpClient::create([
                "verify_peer" => false,
                "verify_host" => false,
                "max_duration" => 0,
                "timeout" => 9999999,
            ])
        );
        $this->websocket = new RatchetWebsocketAdapter([
            'timeout' => 15,
            'tls' => [
                'verify_peer' => false,
                'verify_peer_name' => false,
            ],
        ]);
        $this->options = new Options(getenv('K8S_ENDPOINT'));
        $this->options->setToken(getenv('K8S_ACCESS_TOKEN'));
        $this->options->setHttpClient($this->httpClient);
        $this->options->setWebsocketClient($this->websocket);
        $this->client = new K8s($this->options);
    }

    public function createPods(
        string $baseName,
        int $count,
        string $image = 'nginx:latest',
        ?string $namespace = null
    ): void {
        for ($i = 0; $i < $count; $i++) {
            $this->client->create(new Pod(
                "$baseName-$i",
                [new Container("$baseName-$i", $image)]
            ), [], $namespace);
        }
    }

    public function waitForKind(string $fqcn, int $count): void
    {
        $iterations = 0;

        $list = null;
        do {
            if ($list !== null && $iterations > self::MAX_WAIT_ITERATIONS) {
                throw new \RuntimeException(sprintf(
                    'Max iterations reached for test. Total kind %s out of %s.',
                    count(iterator_to_array($list)),
                    $count
                ));
            }
            sleep(1);
            $list = $this->client->listNamespaced($fqcn);
            $iterations++;
        } while (count(iterator_to_array($list)) < $count);
    }

    public function waitForEmptyKind(string $fqcn): void
    {
        $iterations = 0;

        $list = null;
        do {
            if ($list !== null && $iterations > self::MAX_WAIT_ITERATIONS) {
                throw new \RuntimeException(sprintf(
                    'Max iterations reached for test. Remaining kind: %s',
                    count(iterator_to_array($list))
                ));
            }
            sleep(1);
            $list = $this->client->listNamespaced($fqcn);
            $iterations++;
        } while (count(iterator_to_array($list)) > 0);
    }
}
