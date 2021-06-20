<?php

namespace App\Tests\unit\Service;

use App\Service\CsvFileLoader;
use ArrayIterator;
use Exception;
use Iterator;
use League\Csv\Exception as LeagueCsvException;
use League\Csv\Reader;
use League\Csv\UnavailableStream;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Filesystem\Exception\FileNotFoundException;

class CsvFileLoaderTest extends TestCase
{
    /**
     * @var CsvFileLoader|MockObject
     */
    private $csvFileLoader;

    /**
     * @var Reader|MockObject
     */
    private $reader;

    /**
     * @throws Exception
     */
    public function testLoadFileShouldFailOnFileNotFoundException()
    {
        $this->expectException(FileNotFoundException::class);
        $this->expectExceptionMessage('File filename could not be found.');

        $this->csvFileLoader
            ->method('createReader')
            ->willThrowException(UnavailableStream::dueToPathNotFound(''));

        $this->csvFileLoader->loadFile('filename');
    }

    public function testLoadFileShouldFailOnFileLeagueException()
    {
        $this->expectException(Exception::class);

        $this->csvFileLoader
            ->method('createReader')
            ->willThrowException(new LeagueCsvException());

        $this->csvFileLoader->loadFile('filename');
    }

    /**
     * @throws Exception
     */
    public function testLoadFileShouldPass()
    {
        $this->reader->method('count')->willReturn(10);
        $this->reader->expects($this->once())->method('skipEmptyRecords');


        $result = $this->csvFileLoader->loadFile('filename');

        $this->assertSame(10, $result);
    }

    /**
     * @throws Exception
     */
    public function testCountShouldReturnReaderCounter()
    {
        $this->reader->method('count')->willReturn(10);

        $result = $this->csvFileLoader->count();
        $this->assertNull($result);

        $this->csvFileLoader->loadFile('filename');

        $result = $this->csvFileLoader->count();
        $this->assertSame(10, $result);
    }

    /**
     * @throws Exception
     */
    public function testReadShouldReturnRecords()
    {
        $this->csvFileLoader
            ->method('processStatement')
            ->willReturn(new ArrayIterator([[],
                [],
                []
            ]));

        $this->csvFileLoader->loadFile('dsadsa');
        $result = $this->csvFileLoader->read();
        $this->assertInstanceOf(Iterator::class, $result);
        $this->assertCount(3, $result);
    }


    protected function setUp(): void
    {
        $this->reader = $this->getMockBuilder(Reader::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['count', 'skipEmptyRecords', 'getRecords'])
            ->getMock();

        $this->csvFileLoader = $this->getMockBuilder(CsvFileLoader::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['createReader', 'processStatement'])
            ->getMock();

        $this->csvFileLoader
            ->method('createReader')
            ->willReturn($this->reader);
    }
}
