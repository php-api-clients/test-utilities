<?php declare(strict_types=1);

namespace ApiClients\Tests\Tools\TestUtilities;

use ApiClients\Tools\TestUtilities\PhpCsFixerConfig;
use ApiClients\Tools\TestUtilities\TestCase;

final class PhpCsFixerConfigTest extends TestCase
{
    public function testCreate(): void
    {
        $ruleName = 'extra_rule_iwufhkyqwehifgyuqewf';
        $config = PhpCsFixerConfig::create([$ruleName => true]);
        self::assertTrue(isset($config->getRules()[$ruleName]));
    }
}
