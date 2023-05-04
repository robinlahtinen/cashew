<?php


namespace Kernel;

use Cashew\Kernel\Kernel;
use PHPUnit\Framework\TestCase;

class KernelTest extends TestCase {
    /**
     * @covers \Cashew\Kernel\Kernel::getInstance
     * @covers \Cashew\Kernel\Kernel::setInstance
     */
    public function testInstance() {
        Kernel::setInstance(new Kernel());

        $expected = Kernel::class;
        $actual = get_class(Kernel::getInstance());

        $this->assertEquals($expected, $actual);
    }
}
