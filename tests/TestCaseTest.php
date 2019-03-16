<?php declare(strict_types=1);

namespace ApiClients\Tests\Tools\TestUtilities;

use ApiClients\Tools\TestUtilities\TestCase;
use React\EventLoop\Factory;
use React\EventLoop\LoopInterface;
use React\EventLoop\StreamSelectLoop;
use React\Promise\Deferred;
use React\Promise\Timer\TimeoutException;
use function React\Promise\resolve;

final class TestCaseTest extends TestCase
{
    private const PENTIUM = 66;

    /**
     * @var string
     */
    private $previousTemporaryDirectory = '';

    public function provideTemporaryDirectory(): iterable
    {
        for ($i = 0; $i <= self::PENTIUM; $i++) {
            yield [
                (string) random_int($i * $i, PHP_INT_MAX),
            ];
        }
    }

    public function provideEventLoop(): iterable
    {
        yield [null];
        yield [Factory::create()];
        yield [new StreamSelectLoop()];
    }

    public function testRecursiveDirectoryCreation(): void
    {
        static::assertFileExists($this->getTmpDir());
    }

    /**
     * @dataProvider provideTemporaryDirectory
     */
    public function testTemporaryDirectoryAndGetFilesInDirectory(string $int): void
    {
        static::assertNotSame($this->getTmpDir(), $this->previousTemporaryDirectory);

        $dir = $this->getTmpDir() . $this->getRandomNameSpace() . DIRECTORY_SEPARATOR;
        mkdir($dir);

        for ($i = 0; $i < self::PENTIUM; $i++) {
            static::assertCount($i, $this->getFilesInDirectory($this->getTmpDir()), (string)$i);
            file_put_contents($dir . $i, $int);
        }

        static::assertCount(self::PENTIUM, $this->getFilesInDirectory($this->getTmpDir()));

        foreach ($this->getFilesInDirectory($this->getTmpDir()) as $file) {
            static::assertSame($int, file_get_contents($file));
        }
    }

    /**
     * @dataProvider provideEventLoop
     * @param LoopInterface|null $loop
     */
    public function testAwait(?LoopInterface $loop): void
    {
        $value = time();
        static::assertSame($value, $this->await(resolve($value), $loop));
    }

    /**
     * @dataProvider provideEventLoop
     * @param LoopInterface|null $loop
     */
    public function testAwaitAll(?LoopInterface $loop): void
    {
        $value = time();
        static::assertSame([$value, $value], $this->awaitAll([resolve($value), resolve($value)], $loop));
    }

    /**
     * @dataProvider provideEventLoop
     * @param LoopInterface|null $loop
     */
    public function testAwaitAny(?LoopInterface $loop): void
    {
        $value = time();
        static::assertSame($value, $this->awaitAny([resolve($value), resolve($value)], $loop));
    }

    /**
     * @dataProvider provideTrueFalse
     * @param mixed $bool
     */
    public function testTrueFalse($bool): void
    {
        static::assertIsBool($bool);
    }

    /**
     * @dataProvider provideEventLoop
     */
    public function testAwaitTimeout(?LoopInterface $loop): void
    {
        self::expectException(TimeoutException::class);

        $this->await((new Deferred())->promise(), $loop, 0.1);
    }

    public function testGetSysTempDir(): void
    {
        self::assertFileExists($this->getSysTempDir());
    }
}
