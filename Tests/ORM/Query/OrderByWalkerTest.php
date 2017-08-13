<?php

/*
 * This file is part of the Sonatra package.
 *
 * (c) François Pluchino <francois.pluchino@sonatra.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonatra\Component\DoctrineExtensions\Tests\ORM\Query;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Query;
use Sonatra\Component\DoctrineExtensions\ORM\Query\OrderByWalker;
use Sonatra\Component\DoctrineExtensions\Tests\OrmTestCase;

/**
 * Tests case for order by walker.
 *
 * @author François Pluchino <francois.pluchino@sonatra.com>
 */
class OrderByWalkerTest extends OrmTestCase
{
    /**
     * @var EntityManagerInterface
     */
    private $em;

    protected function setUp()
    {
        $this->em = $this->_getTestEntityManager();
    }

    public function testOrderSingleField()
    {
        $dqlToBeTested = 'SELECT u FROM Sonatra\Component\DoctrineExtensions\Tests\Models\UserMock u';
        $treeWalkers = array(OrderByWalker::class);

        $query = $this->em->createQuery($dqlToBeTested);
        $query->setHint(Query::HINT_CUSTOM_TREE_WALKERS, $treeWalkers)
            ->useQueryCache(false);

        $query->setHint(OrderByWalker::HINT_SORT_ALIAS, array('u'));
        $query->setHint(OrderByWalker::HINT_SORT_FIELD, array('username'));
        $query->setHint(OrderByWalker::HINT_SORT_DIRECTION, array('desc'));

        $expected = 'SELECT u0_.id AS id_0, u0_.username AS username_1 FROM users u0_ ORDER BY u0_.username DESC';
        $this->assertSame($expected, $query->getSQL());
    }

    public function testOrderMultipleFields()
    {
        $dqlToBeTested = 'SELECT u FROM Sonatra\Component\DoctrineExtensions\Tests\Models\UserMock u';
        $treeWalkers = array(OrderByWalker::class);

        $query = $this->em->createQuery($dqlToBeTested);
        $query->setHint(Query::HINT_CUSTOM_TREE_WALKERS, $treeWalkers)
            ->useQueryCache(false);

        $query->setHint(OrderByWalker::HINT_SORT_ALIAS, array('u', 'u'));
        $query->setHint(OrderByWalker::HINT_SORT_FIELD, array('username', 'id'));
        $query->setHint(OrderByWalker::HINT_SORT_DIRECTION, array('desc', 'asc'));

        $expected = 'SELECT u0_.id AS id_0, u0_.username AS username_1 FROM users u0_ ORDER BY u0_.username DESC, u0_.id ASC';
        $this->assertSame($expected, $query->getSQL());
    }

    public function testOrderWithoutField()
    {
        $dqlToBeTested = 'SELECT u FROM Sonatra\Component\DoctrineExtensions\Tests\Models\UserMock u';
        $treeWalkers = array(OrderByWalker::class);

        $query = $this->em->createQuery($dqlToBeTested);
        $query->setHint(Query::HINT_CUSTOM_TREE_WALKERS, $treeWalkers)
            ->useQueryCache(false);

        $expected = 'SELECT u0_.id AS id_0, u0_.username AS username_1 FROM users u0_';
        $this->assertSame($expected, $query->getSQL());
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage The HINT_SORT_ALIAS and HINT_SORT_DIRECTION must be an array
     */
    public function testOrderWithInvalidAliases()
    {
        $dqlToBeTested = 'SELECT u FROM Sonatra\Component\DoctrineExtensions\Tests\Models\UserMock u';
        $treeWalkers = array(OrderByWalker::class);

        $query = $this->em->createQuery($dqlToBeTested);
        $query->setHint(Query::HINT_CUSTOM_TREE_WALKERS, $treeWalkers)
            ->useQueryCache(false);

        $query->setHint(OrderByWalker::HINT_SORT_ALIAS, 'u');
        $query->setHint(OrderByWalker::HINT_SORT_FIELD, array('username'));
        $query->setHint(OrderByWalker::HINT_SORT_DIRECTION, 'desc');

        $query->getSQL();
    }

    /**
     * @expectedException \UnexpectedValueException
     * @expectedExceptionMessage There is no component aliased by [a] in the given Query
     */
    public function testOrderWithInvalidAliasComponent()
    {
        $dqlToBeTested = 'SELECT u FROM Sonatra\Component\DoctrineExtensions\Tests\Models\UserMock u';
        $treeWalkers = array(OrderByWalker::class);

        $query = $this->em->createQuery($dqlToBeTested);
        $query->setHint(Query::HINT_CUSTOM_TREE_WALKERS, $treeWalkers)
            ->useQueryCache(false);

        $query->setHint(OrderByWalker::HINT_SORT_ALIAS, array('a'));
        $query->setHint(OrderByWalker::HINT_SORT_FIELD, array('username'));
        $query->setHint(OrderByWalker::HINT_SORT_DIRECTION, array('desc'));

        $query->getSQL();
    }

    /**
     * @expectedException \UnexpectedValueException
     * @expectedExceptionMessage There is no such field [foo] in the given Query component, aliased by [u]
     */
    public function testOrderWithInvalidField()
    {
        $dqlToBeTested = 'SELECT u FROM Sonatra\Component\DoctrineExtensions\Tests\Models\UserMock u';
        $treeWalkers = array(OrderByWalker::class);

        $query = $this->em->createQuery($dqlToBeTested);
        $query->setHint(Query::HINT_CUSTOM_TREE_WALKERS, $treeWalkers)
            ->useQueryCache(false);

        $query->setHint(OrderByWalker::HINT_SORT_ALIAS, array('u'));
        $query->setHint(OrderByWalker::HINT_SORT_FIELD, array('foo'));
        $query->setHint(OrderByWalker::HINT_SORT_DIRECTION, array('desc'));

        $query->getSQL();
    }

    /**
     * @expectedException \UnexpectedValueException
     * @expectedExceptionMessage There is no component field [username] in the given Query
     */
    public function testOrderWithoutAliasAndComponent()
    {
        $dqlToBeTested = 'SELECT u FROM Sonatra\Component\DoctrineExtensions\Tests\Models\UserMock u';
        $treeWalkers = array(OrderByWalker::class);

        $query = $this->em->createQuery($dqlToBeTested);
        $query->setHint(Query::HINT_CUSTOM_TREE_WALKERS, $treeWalkers)
            ->useQueryCache(false);

        $query->setHint(OrderByWalker::HINT_SORT_ALIAS, array(false));
        $query->setHint(OrderByWalker::HINT_SORT_FIELD, array('username'));
        $query->setHint(OrderByWalker::HINT_SORT_DIRECTION, array('desc'));

        $query->getSQL();
    }
}
