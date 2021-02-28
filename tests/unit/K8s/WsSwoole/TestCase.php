<?php

declare(strict_types=1);

namespace unit\K8s\WsSwoole;

use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use PHPUnit\Framework\TestCase as BaseTestCase;

class TestCase extends BaseTestCase
{
    use MockeryPHPUnitIntegration;
}
