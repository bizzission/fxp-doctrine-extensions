<?php

/*
 * This file is part of the Sonatra package.
 *
 * (c) François Pluchino <francois.pluchino@sonatra.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonatra\Component\DoctrineExtensions\Tests\Validator\Constraints;

use Doctrine\Common\Persistence\Mapping\ClassMetadata;
use PHPUnit\Framework\TestCase;
use Sonatra\Component\DoctrineExtensions\Validator\Constraints\Util;

/**
 * Tests case for util.
 *
 * @author François Pluchino <francois.pluchino@sonatra.com>
 */
class UtilTest extends TestCase
{
    public function getIdentifierTypes()
    {
        return array(
            array('bigint', 0),
            array('decimal', 0),
            array('integer', 0),
            array('smallint', 0),
            array('float', 0),
            array('guid', '00000000-0000-0000-0000-000000000000'),
            array('other', ''),
        );
    }

    /**
     * @dataProvider getIdentifierTypes
     *
     * @param string     $identifierType
     * @param string|int $expected
     */
    public function testFormatEmptyIdentifier($identifierType, $expected)
    {
        /* @var ClassMetadata|\PHPUnit_Framework_MockObject_MockObject $meta */
        $meta = $this->getMockBuilder(ClassMetadata::class)->getMock();
        $meta->expects($this->any())
            ->method('getIdentifier')
            ->willReturn(array('id'));

        $meta->expects($this->any())
            ->method('getTypeOfField')
            ->with('id')
            ->willReturn($identifierType);

        $this->assertSame($expected, Util::formatEmptyIdentifier($meta));
    }

    /**
     * @dataProvider getIdentifierTypes
     *
     * @param string     $identifierType
     * @param string|int $expected
     */
    public function testGetFormattedIdentifier($identifierType, $expected)
    {
        $fieldName = 'single';
        $value = null;
        $criteria = array(
            $fieldName => new \stdClass(),
        );

        /* @var ClassMetadata|\PHPUnit_Framework_MockObject_MockObject $meta */
        $meta = $this->getMockBuilder(ClassMetadata::class)->getMock();
        $meta->expects($this->any())
            ->method('getIdentifier')
            ->willReturn(array($fieldName));

        $meta->expects($this->any())
            ->method('getTypeOfField')
            ->with($fieldName)
            ->willReturn($identifierType);

        $this->assertSame($expected, Util::getFormattedIdentifier($meta, $criteria, $fieldName, $value));
    }
}
