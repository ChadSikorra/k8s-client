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

namespace unit\K8s\Client\Patch\Operation;

use K8s\Client\Patch\Operation\Replace;
use unit\K8s\Client\TestCase;

class ReplaceTest extends TestCase
{
    /**
     * @var Replace
     */
    private $subject;

    public function setUp(): void
    {
        parent::setUp();
        $this->subject = new Replace('/foo', 'bar');
    }

    public function testItHasTheRightOp(): void
    {
        $this->assertEquals('replace', $this->subject->getOp());
    }

    public function testGetValue(): void
    {
        $this->assertEquals('bar', $this->subject->getValue());
    }

    public function testSetValue(): void
    {
        $this->subject->setValue(true);

        $this->assertEquals(true, $this->subject->getValue());
    }

    public function testGetPath(): void
    {
        $this->assertEquals('/foo', $this->subject->getPath());
    }

    public function testToArray(): void
    {
        $expected = [
            'op' => 'replace',
            'path' => '/foo',
            'value' => 'bar',
        ];

        $this->assertEquals($expected, $this->subject->toArray());
    }
}
