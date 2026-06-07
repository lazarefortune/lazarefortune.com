<?php

namespace App\Tests;

use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class ApplicationTest extends KernelTestCase
{
    public function testKernelBoots(): void
    {
        self::bootKernel();
        $this->assertNotNull(self::getContainer()->get('kernel'));
    }
}
