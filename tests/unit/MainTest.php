<?php


namespace Smoren\Containers\Tests\Unit;

use Codeception\Lib\Console\Output;
use Codeception\Test\Unit;
use Exception;
use Smoren\Containers\Exceptions\GraphException;
use Smoren\Containers\Structs\Graph;
use Smoren\Containers\Structs\GraphLink;
use Smoren\ExtendedExceptions\BaseException;
use Smoren\Containers\Exceptions\LinkedListException;
use Smoren\Containers\Exceptions\MappedCollectionException;
use Smoren\Containers\Exceptions\MappedLinkedListException;
use Smoren\Containers\Structs\LinkedList;
use Smoren\Containers\Structs\LinkedListItem;
use Smoren\Containers\Structs\MappedCollection;
use Smoren\Containers\Structs\MappedLinkedList;
use Smoren\Containers\Structs\SortedMappedLinkedList;
use Smoren\Containers\Tests\Unit\Utility\ArraySortedMappedLinkedList;
use Smoren\Containers\Tests\Unit\Utility\IntegerSortedLinkedList;

class MainTest extends Unit
{
    /**
     * @throws MappedCollectionException
     */
    public function testMappedCollection()
    {
        $coll = new MappedCollection();
        $this->assertEquals(0, $coll->count());
        $coll->add('1', ['id' => 1]);
        $coll->add('2', ['id' => 2]);
        $this->assertEquals(2, $coll->count());

        $this->assertTrue($coll->exist(1));
        $this->assertTrue($coll->exist(2));
        $this->assertFalse($coll->exist(3));

        try {
            $coll->add('1', ['id' => 3]);
            $this->assertTrue(false);
        } catch(MappedCollectionException $e) {
            $this->assertEquals(MappedCollectionException::STATUS_ID_EXIST, $e->getCode());
            $this->assertEquals(2, $coll->count());
        }

        try {
            $coll->get('3');
            $this->assertTrue(false);
        } catch(MappedCollectionException $e) {
            $this->assertEquals(MappedCollectionException::STATUS_ID_NOT_EXIST, $e->getCode());
            $this->assertEquals(2, $coll->count());
        }
        try {
            $coll->delete('3');
            $this->assertTrue(false);
        } catch(MappedCollectionException $e) {
            $this->assertEquals(MappedCollectionException::STATUS_ID_NOT_EXIST, $e->getCode());
            $this->assertEquals(2, $coll->count());
        }

        $coll->delete('2');
        $this->assertEquals(1, $coll->count());

        try {
            $coll->get('2');
            $this->assertTrue(false);
        } catch(MappedCollectionException $e) {
            $this->assertEquals(MappedCollectionException::STATUS_ID_NOT_EXIST, $e->getCode());
            $this->assertEquals(1, $coll->count());
        }

        $coll
            ->add(2, ['id' => 2])
            ->add(3, ['id' => 3])
            ->add(4, ['id' => 4])
            ->add(5, ['id' => 5]);

        $this->assertEquals(5, $coll->count());

        $etalon = [
            ['id' => 1],
            ['id' => 2],
            ['id' => 3],
            ['id' => 4],
            ['id' => 5],
        ];
        $i = 1;
        foreach($coll as $id => $item) {
            $this->assertEquals($i++, $id);
            $this->assertEquals(array_shift($etalon), $item);
        }

        $coll->clear();
        $this->assertEquals(0, $coll->count());
        $this->assertEquals([], $coll->toArray());
    }

    /**
     * @throws LinkedListException
     * @throws Exception
     */
    public function testLinkedList()
    {
        $ll = new LinkedList();
        $this->assertEquals(0, $ll->count());

        $ll->pushBack(1);
        $this->assertEquals(1, $ll->count());
        $this->assertEquals(1, $ll->popFront());
        $this->assertEquals(0, $ll->count());

        $ll->pushFront(0);
        $ll->pushFront(1);
        $ll->pushFront(2);

        $r = [];
        foreach($ll as $key => $value) {
            $this->assertTrue($key instanceof LinkedListItem);
            $r[] = $value;
        }
        $this->assertEquals([2, 1, 0], $r);

        $ll->pushBack(-1);
        $ll->pushBack(1);
        $ll->pushBack(-2);
        $ll->pushBack(2);

        $this->assertEquals([2, 1, 0, -1, 1, -2, 2], $ll->toArray());
        $this->assertEquals(7, $ll->count());

        $ll->sort(function($lhs, $rhs) {
            return $lhs > $rhs;
        });
        $this->assertEquals([-2, -1, 0, 1, 1, 2, 2], $ll->toArray());
        $this->assertEquals(7, $ll->count());

        $ll->swap($ll->getFirst(), $ll->getLast());
        $this->assertEquals([2, -1, 0, 1, 1, 2, -2], $ll->toArray());
        $this->assertEquals(7, $ll->count());

        $ll->swap($ll->getFirst(), $ll->getFirst()->getNext());
        $this->assertEquals([-1, 2, 0, 1, 1, 2, -2], $ll->toArray());
        $this->assertEquals(7, $ll->count());

        $ll->popFront();
        $this->assertEquals([2, 0, 1, 1, 2, -2], $ll->toArray());
        $this->assertEquals(6, $ll->count());

        $ll->popBack();
        $this->assertEquals([2, 0, 1, 1, 2], $ll->toArray());
        $this->assertEquals(5, $ll->count());

        $ll->delete($ll->getFirst()->getNext()->getNext());
        $this->assertEquals([2, 0, 1, 2], $ll->toArray());
        $this->assertEquals(4, $ll->count());

        $ll = LinkedList::merge(new LinkedList([-99, -98]), $ll, new LinkedList([98, 99]));
        $this->assertEquals([-99, -98, 2, 0, 1, 2, 98, 99], $ll->toArray());
        $this->assertEquals(8, $ll->count());

        $ll->pushBefore($ll->getFirst(), -101);
        $ll->pushBefore($ll->getFirst()->getNext(), -100);

        $this->assertEquals([-101, -100, -99, -98, 2, 0, 1, 2, 98, 99], $ll->toArray());
        $this->assertEquals(10, $ll->count());

        $ll->pushAfter($ll->getLast(), 100);
        $this->assertEquals([-101, -100, -99, -98, 2, 0, 1, 2, 98, 99, 100], $ll->toArray());
        $this->assertEquals(11, $ll->count());

        $ll->pushAfter($ll->getLast()->getPrev()->getPrev()->getPrev(), 97);
        $this->assertEquals([-101, -100, -99, -98, 2, 0, 1, 2, 97, 98, 99, 100], $ll->toArray());
        $this->assertEquals(12, $ll->count());

        $ll->clear();
        $this->assertEquals([], $ll->toArray());
        $this->assertEquals(0, $ll->count());

        try {
            $ll->popBack();
            $this->assertTrue(false);
        } catch(LinkedListException $e) {
            $this->assertEquals(LinkedListException::STATUS_EMPTY, $e->getCode());
        }

        try {
            $ll->popFront();
            $this->assertTrue(false);
        } catch(LinkedListException $e) {
            $this->assertEquals(LinkedListException::STATUS_EMPTY, $e->getCode());
        }

        $ll = new LinkedList(['a1', 'a2', 'a3']);
        $this->assertEquals(['a1', 'a2', 'a3'], $ll->toArray());
        $this->assertEquals(3, $ll->count());
    }

    /**
     * @throws LinkedListException
     * @throws MappedCollectionException
     * @throws MappedLinkedListException
     */
    public function testMappedLinkedList()
    {
        $ll = new MappedLinkedList();
        $this->assertEquals(0, $ll->count());

        $ll->pushBack(101, 1);
        $this->assertEquals(1, $ll->count());
        $this->assertEquals([101, 1], $ll->popFront());
        $this->assertEquals(0, $ll->count());

        $ll->pushFront(100, 0);
        $ll->pushFront(101, 1);
        $ll->pushFront(102, 2);

        $r = [];
        foreach($ll as $id => $value) {
            $this->assertTrue(is_string($id));
            $r[$id] = $value;
        }
        $this->assertEquals([102 => 2, 101 => 1, 100 => 0], $r);

        $ll->pushBack(-110, -10);
        $ll->pushBack(110, 10);
        $ll->pushBack(-120, -20);
        $ll->pushBack(120, 20);

        $this->assertEquals([102, 101, 100, -110, 110, -120, 120], array_keys($ll->toArray()));
        $this->assertEquals([2, 1, 0, -10, 10, -20, 20], array_values($ll->toArray()));
        $this->assertEquals(7, $ll->count());

        $ll->sort(function($lhs, $rhs) use ($ll) {
            return $lhs > $rhs;
        });
        $this->assertEquals([-120, -110, 100, 101, 102, 110, 120], array_keys($ll->toArray()));
        $this->assertEquals([-20, -10, 0, 1, 2, 10, 20], array_values($ll->toArray()));
        $this->assertEquals(7, $ll->count());

        $ll->sort(function($lhs, $rhs) use ($ll) {
            return $lhs < $rhs;
        });
        $this->assertEquals([120, 110, 102, 101, 100, -110, -120], array_keys($ll->toArray()));
        $this->assertEquals([20, 10, 2, 1, 0, -10, -20], array_values($ll->toArray()));
        $this->assertEquals(7, $ll->count());

        $ll->sort(function($lhs, $rhs) use ($ll) {
            return $lhs > $rhs;
        });
        $this->assertEquals([-120, -110, 100, 101, 102, 110, 120], array_keys($ll->toArray()));
        $this->assertEquals([-20, -10, 0, 1, 2, 10, 20], array_values($ll->toArray()));
        $this->assertEquals(7, $ll->count());

        try {
            $ll->swap(-121, 110);
            $this->assertTrue(false);
        } catch(MappedLinkedListException $e) {
            $this->assertEquals(MappedLinkedListException::STATUS_ID_NOT_EXIST, $e->getCode());
        }

        try {
            $ll->swap(-120, 111);
            $this->assertTrue(false);
        } catch(MappedLinkedListException $e) {
            $this->assertEquals(MappedLinkedListException::STATUS_ID_NOT_EXIST, $e->getCode());
        }

        try {
            $ll->swap(-121, 111);
            $this->assertTrue(false);
        } catch(MappedLinkedListException $e) {
            $this->assertEquals(MappedLinkedListException::STATUS_ID_NOT_EXIST, $e->getCode());
        }

        $ll->swap(-120, 110);
        $this->assertEquals([110, -110, 100, 101, 102, -120, 120], array_keys($ll->toArray()));
        $this->assertEquals([10, -10, 0, 1, 2, -20, 20], array_values($ll->toArray()));
        $this->assertEquals(7, $ll->count());

        $ll->swap(-120, 120);
        $this->assertEquals([110, -110, 100, 101, 102, 120, -120], array_keys($ll->toArray()));
        $this->assertEquals([10, -10, 0, 1, 2, 20, -20], array_values($ll->toArray()));

        $ll->swap(120, 120);
        $this->assertEquals([110, -110, 100, 101, 102, 120, -120], array_keys($ll->toArray()));
        $this->assertEquals([10, -10, 0, 1, 2, 20, -20], array_values($ll->toArray()));

        $ll->swap(-120, -120);
        $this->assertEquals([110, -110, 100, 101, 102, 120, -120], array_keys($ll->toArray()));
        $this->assertEquals([10, -10, 0, 1, 2, 20, -20], array_values($ll->toArray()));

        $ll->swap(110, 110);
        $this->assertEquals([110, -110, 100, 101, 102, 120, -120], array_keys($ll->toArray()));
        $this->assertEquals([10, -10, 0, 1, 2, 20, -20], array_values($ll->toArray()));

        $ll->swap(110, -120);
        $this->assertEquals([-120, -110, 100, 101, 102, 120, 110], array_keys($ll->toArray()));
        $this->assertEquals([-20, -10, 0, 1, 2, 20, 10], array_values($ll->toArray()));

        $ll->swap(-120, -110);
        $this->assertEquals([-110, -120, 100, 101, 102, 120, 110], array_keys($ll->toArray()));
        $this->assertEquals([-10, -20, 0, 1, 2, 20, 10], array_values($ll->toArray()));

        $ll->swap(120, 110);
        $this->assertEquals([-110, -120, 100, 101, 102, 110, 120], array_keys($ll->toArray()));
        $this->assertEquals([-10, -20, 0, 1, 2, 10, 20], array_values($ll->toArray()));

        $ll->sort(function($lhs, $rhs) use ($ll) {
            return $lhs > $rhs;
        });
        $this->assertEquals([-120, -110, 100, 101, 102, 110, 120], array_keys($ll->toArray()));
        $this->assertEquals([-20, -10, 0, 1, 2, 10, 20], array_values($ll->toArray()));
        $this->assertEquals(7, $ll->count());

        $this->assertEquals([-120, -20], $ll->popFront());
        $this->assertEquals([-110, 100, 101, 102, 110, 120], array_keys($ll->toArray()));
        $this->assertEquals([-10, 0, 1, 2, 10, 20], array_values($ll->toArray()));
        $this->assertEquals(6, $ll->count());

        $this->assertEquals([120, 20], $ll->popBack());
        $this->assertEquals([-110, 100, 101, 102, 110], array_keys($ll->toArray()));
        $this->assertEquals([-10, 0, 1, 2, 10], array_values($ll->toArray()));
        $this->assertEquals(5, $ll->count());

        $this->assertEquals(1, $ll->delete(101)->getData());
        $this->assertEquals([-110, 100, 102, 110], array_keys($ll->toArray()));
        $this->assertEquals([-10, 0, 2, 10], array_values($ll->toArray()));
        $this->assertEquals(4, $ll->count());

        $ll = MappedLinkedList::merge(
            new MappedLinkedList([-999 => -99, -998 => -98]),
            $ll,
            new MappedLinkedList([998 => 98, 999 => 99])
        );
        $this->assertEquals([-999, -998, -110, 100, 102, 110, 998, 999], array_keys($ll->toArray()));
        $this->assertEquals([-99, -98, -10, 0, 2, 10, 98, 99], array_values($ll->toArray()));
        $this->assertEquals(8, $ll->count());

        $ll->pushBefore(-110, -111, -11);
        $ll->pushBefore(998, 997, 97);

        try {
            $ll->pushBefore(1000, 1001, 100);
            $this->assertTrue(false);
        } catch(MappedLinkedListException $e) {
            $this->assertEquals(MappedLinkedListException::STATUS_ID_NOT_EXIST, $e->getCode());
        }

        try {
            $ll->pushBefore(998, 997, 100);
            $this->assertTrue(false);
        } catch(MappedLinkedListException $e) {
            $this->assertEquals(MappedLinkedListException::STATUS_ID_EXIST, $e->getCode());
        }

        try {
            $ll->pushBefore(1000, 997, 100);
            $this->assertTrue(false);
        } catch(MappedLinkedListException $e) {
            $this->assertEquals(MappedLinkedListException::STATUS_ID_NOT_EXIST, $e->getCode());
        }

        $this->assertEquals([-999, -998, -111, -110, 100, 102, 110, 997, 998, 999], array_keys($ll->toArray()));
        $this->assertEquals([-99, -98, -11, -10, 0, 2, 10, 97, 98, 99], array_values($ll->toArray()));
        $this->assertEquals(10, $ll->count());

        $ll->pushAfter(102, 103, 3);
        $this->assertEquals([-999, -998, -111, -110, 100, 102, 103, 110, 997, 998, 999], array_keys($ll->toArray()));
        $this->assertEquals([-99, -98, -11, -10, 0, 2, 3, 10, 97, 98, 99], array_values($ll->toArray()));
        $this->assertEquals(11, $ll->count());

        $ll->pushAfter(999, 1000, 100);
        $this->assertEquals([-999, -998, -111, -110, 100, 102, 103, 110, 997, 998, 999, 1000], array_keys($ll->toArray()));
        $this->assertEquals([-99, -98, -11, -10, 0, 2, 3, 10, 97, 98, 99, 100], array_values($ll->toArray()));
        $this->assertEquals(12, $ll->count());

        try {
            $ll->pushAfter(2000, 2001, 2001);
            $this->assertTrue(false);
        } catch(MappedLinkedListException $e) {
            $this->assertEquals(MappedLinkedListException::STATUS_ID_NOT_EXIST, $e->getCode());
        }

        try {
            $ll->pushAfter(999, 1000, 1000);
            $this->assertTrue(false);
        } catch(MappedLinkedListException $e) {
            $this->assertEquals(MappedLinkedListException::STATUS_ID_EXIST, $e->getCode());
        }

        try {
            $ll->pushBefore(2000, 1000, 1000);
            $this->assertTrue(false);
        } catch(MappedLinkedListException $e) {
            $this->assertEquals(MappedLinkedListException::STATUS_ID_NOT_EXIST, $e->getCode());
        }

        $ll->clear();
        $this->assertEquals([], $ll->toArray());
        $this->assertEquals(0, $ll->count());

        $ll = new MappedLinkedList(['a1' => 'b1', 'a2' => 'b2', 'a3' => 'b3']);
        $this->assertEquals(['a1', 'a2', 'a3'], array_keys($ll->toArray()));
        $this->assertEquals(['b1', 'b2', 'b3'], array_values($ll->toArray()));
        $this->assertEquals(3, $ll->count());
    }

    /**
     * @throws LinkedListException
     * @throws Exception
     */
    public function testSortedLinkedList()
    {
        $ll = new IntegerSortedLinkedList([2, 5, 1]);
        $this->assertCount(3, $ll);
        $this->assertEquals([1, 2, 5], $ll->toArray());

        $ll->insert(2);
        $this->assertCount(4, $ll);
        $this->assertEquals([1, 2, 2, 5], $ll->toArray());

        $ll->insert(4);
        $this->assertCount(5, $ll);
        $this->assertEquals([1, 2, 2, 4, 5], $ll->toArray());

        $ll->insert(6);
        $this->assertCount(6, $ll);
        $this->assertEquals([1, 2, 2, 4, 5, 6], $ll->toArray());

        $ll->insert(0);
        $this->assertCount(7, $ll);
        $this->assertEquals([0, 1, 2, 2, 4, 5, 6], $ll->toArray());

        $pos = $ll->insert(3);
        $this->assertCount(8, $ll);
        $this->assertEquals([0, 1, 2, 2, 3, 4, 5, 6], $ll->toArray());

        $ll->delete($pos);
        $this->assertCount(7, $ll);
        $this->assertEquals([0, 1, 2, 2, 4, 5, 6], $ll->toArray());

        $ll->popBack();
        $this->assertCount(6, $ll);
        $this->assertEquals([0, 1, 2, 2, 4, 5], $ll->toArray());

        $ll->popFront();
        $this->assertCount(5, $ll);
        $this->assertEquals([1, 2, 2, 4, 5], $ll->toArray());

        for($i=0; $i<5; ++$i) {
            $ll->popBack();
        }

        $this->assertCount(0, $ll);
        $this->assertEquals([], $ll->toArray());

        try {
            $ll->popBack();
            $this->assertTrue(false);
        } catch(LinkedListException $e) {
            $this->assertEquals(LinkedListException::STATUS_EMPTY, $e->getCode());
        }

        try {
            $ll->popFront();
            $this->assertTrue(false);
        } catch(LinkedListException $e) {
            $this->assertEquals(LinkedListException::STATUS_EMPTY, $e->getCode());
        }

        $ll->insert(10);
        $ll->insert(-1);
        $ll->insert(150);
        $ll->insert(45);
        $ll->insert(36);
        $ll->insert(0);

        $this->assertCount(6, $ll);
        $this->assertEquals([-1, 0, 10, 36, 45, 150], $ll->toArray());

        $ll->clear();
        $this->assertCount(0, $ll);
        $this->assertEquals([], $ll->toArray());
    }

    /**
     * @throws LinkedListException
     * @throws Exception
     */
    public function testMappedSortedLinkedList()
    {
        $ll = new SortedMappedLinkedList([1 => -1, 10 => -10, 5 => -5, 6 => -6, 0 => 0]);
        $this->assertEquals([0, 1, 5, 6, 10], array_keys($ll->toArray()));
        $this->assertEquals([0, -1, -5, -6, -10], array_values($ll->toArray()));

        $ll->insert(3, -3);
        $this->assertEquals([0, 1, 3, 5, 6, 10], array_keys($ll->toArray()));
        $this->assertEquals([0, -1, -3, -5, -6, -10], array_values($ll->toArray()));

        $ll->popBack();
        $ll->popFront();
        $this->assertEquals([1, 3, 5, 6], array_keys($ll->toArray()));
        $this->assertEquals([-1, -3, -5, -6], array_values($ll->toArray()));

        $this->assertEquals(-3, $ll->get(3));
        $this->assertEquals(-1, $ll->get(1));
        $this->assertEquals(-6, $ll->get(6));

        $ll = new ArraySortedMappedLinkedList([
            5 => ['id' => 5],
            1 => ['id' => 1],
            2 => ['id' => 2],
        ]);
        $this->assertCount(3, $ll);
        $this->assertEquals([1, 2, 5], $this->getColumn($ll->toArray(), 'id'));
        $this->assertEquals([1, 2, 5], array_keys($ll->toArray()));

        try {
            $ll->insert(2, ['id' => 2]);
            $this->assertTrue(false);
        } catch(MappedLinkedListException $e) {
            $this->assertEquals(MappedLinkedListException::STATUS_ID_EXIST, $e->getCode());
        }
        $this->assertCount(3, $ll);
        $this->assertEquals([1, 2, 5], $this->getColumn($ll->toArray(), 'id'));
        $this->assertEquals([1, 2, 5], array_keys($ll->toArray()));

        $ll->insert(4, ['id' => 4]);
        $this->assertCount(4, $ll);
        $this->assertEquals([1, 2, 4, 5], $this->getColumn($ll->toArray(), 'id'));
        $this->assertEquals([1, 2, 4, 5], array_keys($ll->toArray()));

        $ll->insert(6, ['id' => 6]);
        $this->assertCount(5, $ll);
        $this->assertEquals([1, 2, 4, 5, 6], $this->getColumn($ll->toArray(), 'id'));
        $this->assertEquals([1, 2, 4, 5, 6], array_keys($ll->toArray()));

        $ll->insert(0, ['id' => 0]);
        $this->assertCount(6, $ll);
        $this->assertEquals([0, 1, 2, 4, 5, 6], $this->getColumn($ll->toArray(), 'id'));
        $this->assertEquals([0, 1, 2, 4, 5, 6], array_keys($ll->toArray()));

        $ll->insert(3, ['id' => 3]);
        $this->assertCount(7, $ll);
        $this->assertEquals([0, 1, 2, 3, 4, 5, 6], $this->getColumn($ll->toArray(), 'id'));
        $this->assertEquals([0, 1, 2, 3, 4, 5, 6], array_keys($ll->toArray()));

        $ll->delete(3);
        $this->assertCount(6, $ll);
        $this->assertEquals([0, 1, 2, 4, 5, 6], $this->getColumn($ll->toArray(), 'id'));
        $this->assertEquals([0, 1, 2, 4, 5, 6], array_keys($ll->toArray()));

        $ll->popBack();
        $this->assertCount(5, $ll);
        $this->assertEquals([0, 1, 2, 4, 5], $this->getColumn($ll->toArray(), 'id'));
        $this->assertEquals([0, 1, 2, 4, 5], array_keys($ll->toArray()));

        $ll->popFront();
        $this->assertCount(4, $ll);
        $this->assertEquals([1, 2, 4, 5], $this->getColumn($ll->toArray(), 'id'));
        $this->assertEquals([1, 2, 4, 5], array_keys($ll->toArray()));

        for($i=0; $i<4; ++$i) {
            $ll->popBack();
        }

        $this->assertCount(0, $ll);
        $this->assertEquals([], $ll->toArray());

        try {
            $ll->popBack();
            $this->assertTrue(false);
        } catch(MappedLinkedListException $e) {
            $this->assertEquals(MappedLinkedListException::STATUS_EMPTY, $e->getCode());
        }

        try {
            $ll->popFront();
            $this->assertTrue(false);
        } catch(MappedLinkedListException $e) {
            $this->assertEquals(MappedLinkedListException::STATUS_EMPTY, $e->getCode());
        }

        $ll->insert(10, ['id' => 10]);
        $ll->insert(-1, ['id' => -1]);
        $ll->insert(150, ['id' => 150]);
        $ll->insert(45, ['id' => 45]);
        $ll->insert(36, ['id' => 36]);
        $ll->insert(0, ['id' => 0]);

        $this->assertCount(6, $ll);
        $this->assertEquals([-1, 0, 10, 36, 45, 150], $this->getColumn($ll->toArray(), 'id'));
        $this->assertEquals([-1, 0, 10, 36, 45, 150], array_keys($ll->toArray()));

        $ll->clear();
        $this->assertCount(0, $ll);
        $this->assertEquals([], $ll->toArray());
    }

    public function testGraph()
    {
        $graph = new Graph(
            [1 => 11, 2 => 22, 3 => 33, 4 => 44, 5 => 55],
            [[1, 2, 'a'], [2, 3, 'a'], [3, 4, 'a'], [3, 5, 'a'], [1, 5, 'a']]
        );

        $this->assertCount(5, $graph);

        try {
            $graph->getItem(6);
            $this->assertTrue(false);
        } catch(GraphException $e) {
            $this->assertEquals(GraphException::STATUS_ID_NOT_EXIST, $e->getCode());
        }

        $this->assertEquals([], $graph->getItem(1)->getPrevItemsMap());
        $this->assertEquals(['a' => [2, 5]], $graph->getItem(1)->getNextItemsMap());

        $this->assertEquals(['a' => [1]], $graph->getItem(2)->getPrevItemsMap());
        $this->assertEquals(['a' => [3]], $graph->getItem(2)->getNextItemsMap());

        $this->assertEquals(['a' => [2]], $graph->getItem(3)->getPrevItemsMap());
        $this->assertEquals(['a' => [4, 5]], $graph->getItem(3)->getNextItemsMap());

        $this->assertEquals(['a' => [3]], $graph->getItem(4)->getPrevItemsMap());
        $this->assertEquals([], $graph->getItem(4)->getNextItemsMap());

        $this->assertEquals(['a' => [3, 1]], $graph->getItem(5)->getPrevItemsMap());
        $this->assertEquals([], $graph->getItem(5)->getNextItemsMap());

        $graph->insert(6, 66);
        $this->assertCount(6, $graph);

        $graph->link(1, 6, 'b');
        $graph->link(6, 5, 'b');

        $this->assertEquals([], $graph->getItem(1)->getPrevItemsMap());
        $this->assertEquals(['a' => [2, 5], 'b' => [6]], $graph->getItem(1)->getNextItemsMap());

        $this->assertEquals(['a' => [1]], $graph->getItem(2)->getPrevItemsMap());
        $this->assertEquals(['a' => [3]], $graph->getItem(2)->getNextItemsMap());

        $this->assertEquals(['a' => [2]], $graph->getItem(3)->getPrevItemsMap());
        $this->assertEquals(['a' => [4, 5]], $graph->getItem(3)->getNextItemsMap());

        $this->assertEquals(['a' => [3]], $graph->getItem(4)->getPrevItemsMap());
        $this->assertEquals([], $graph->getItem(4)->getNextItemsMap());

        $this->assertEquals(['a' => [3, 1], 'b' => [6]], $graph->getItem(5)->getPrevItemsMap());
        $this->assertEquals([], $graph->getItem(5)->getNextItemsMap());

        $this->assertEquals(['b' => [1]], $graph->getItem(6)->getPrevItemsMap());
        $this->assertEquals(['b' => [5]], $graph->getItem(6)->getNextItemsMap());

        $graph->unlink(1, 5);
        $graph->unlink(3, 5);
        $graph->link(6, 3, 'b');
        $graph->link(4, 5, 'b');

        $this->assertEquals([], $graph->getItem(1)->getPrevItemsMap());
        $this->assertEquals(['a' => [2], 'b' => [6]], $graph->getItem(1)->getNextItemsMap());

        $this->assertEquals(['a' => [1]], $graph->getItem(2)->getPrevItemsMap());
        $this->assertEquals(['a' => [3]], $graph->getItem(2)->getNextItemsMap());

        $this->assertEquals(['a' => [2], 'b' => [6]], $graph->getItem(3)->getPrevItemsMap());
        $this->assertEquals(['a' => [4]], $graph->getItem(3)->getNextItemsMap());

        $this->assertEquals(['a' => [3]], $graph->getItem(4)->getPrevItemsMap());
        $this->assertEquals(['b' => [5]], $graph->getItem(4)->getNextItemsMap());

        $this->assertEquals(['b' => [6, 4]], $graph->getItem(5)->getPrevItemsMap());
        $this->assertEquals([], $graph->getItem(5)->getNextItemsMap());

        $this->assertEquals(['b' => [1]], $graph->getItem(6)->getPrevItemsMap());
        $this->assertEquals(['b' => [5, 3]], $graph->getItem(6)->getNextItemsMap());

        $graph->link(4, 5, 'c');

        $this->assertEquals(['b' => [5], 'c' => [5]], $graph->getItem(4)->getNextItemsMap());
        $this->assertEquals(['b' => [5], 'c' => [5]], $graph->getItem(4)->getNextItemsMap(['b', 'c']));
        $this->assertEquals(['b' => [5]], $graph->getItem(4)->getNextItemsMap(['b']));
        $this->assertEquals(['c' => [5]], $graph->getItem(4)->getNextItemsMap(['c']));

        $this->assertEquals(['b' => [6, 4], 'c' => [4]], $graph->getItem(5)->getPrevItemsMap());
        $this->assertEquals(['b' => [6, 4], 'c' => [4]], $graph->getItem(5)->getPrevItemsMap(['b', 'c']));
        $this->assertEquals(['b' => [6, 4]], $graph->getItem(5)->getPrevItemsMap(['b']));
        $this->assertEquals(['c' => [4]], $graph->getItem(5)->getPrevItemsMap(['c']));

        $graph->unlink(4, 5);

        $this->assertEquals([], $graph->getItem(4)->getNextItemsMap());
        $this->assertEquals(['b' => [6]], $graph->getItem(5)->getPrevItemsMap());

        $graph->delete(2);
        $this->assertCount(5, $graph);

        try {
            $graph->getItem(2);
            $this->assertTrue(false);
        } catch(GraphException $e) {
            $this->assertEquals(GraphException::STATUS_ID_NOT_EXIST, $e->getCode());
        }

        $this->assertEquals([], $graph->getItem(1)->getPrevItemsMap());
        $this->assertEquals(['b' => [6]], $graph->getItem(1)->getNextItemsMap());

        $this->assertEquals(['b' => [6]], $graph->getItem(3)->getPrevItemsMap());
        $this->assertEquals(['a' => [4]], $graph->getItem(3)->getNextItemsMap());

        $this->assertEquals(['a' => [3]], $graph->getItem(4)->getPrevItemsMap());
        $this->assertEquals([], $graph->getItem(4)->getNextItemsMap());

        $this->assertEquals(['b' => [6]], $graph->getItem(5)->getPrevItemsMap());
        $this->assertEquals([], $graph->getItem(5)->getNextItemsMap());

        $this->assertEquals(['b' => [1]], $graph->getItem(6)->getPrevItemsMap());
        $this->assertEquals(['b' => [5, 3]], $graph->getItem(6)->getNextItemsMap());

        $graph->clear();
        $this->assertCount(0, $graph);
    }

    /**
     * @throws GraphException
     */
    public function testGraphTraverse()
    {
        $graph = new Graph(
            [1 => 11, 2 => 22, 3 => 33, 4 => 44, 5 => 55, 6 => 66],
            [[1, 2, 'a'], [2, 3, 'a'], [3, 4, 'a'], [2, 5, 'b'], [5, 3, 'b'], [5, 6, 'c'], [6, 4, 'c']]
        );

        /* ================= */
        /* TRAVERSE BACKWARD */
        /* ================= */

        $paths = $graph->traverseBackward(4);
        $this->assertCount(3, $paths);
        $this->assertEquals([4, 3, 2, 1], $paths[0]->toArray(true));
        $this->assertEquals([4, 3, 5, 2, 1], $paths[1]->toArray(true));
        $this->assertEquals([4, 6, 5, 2, 1], $paths[2]->toArray(true));
        $this->assertEquals([1, 2, 5, 6, 4], $paths[2]->reverse()->toArray(true));

        $paths = $graph->traverseBackward(4, ['a', 'b', 'c']);
        $this->assertCount(3, $paths);
        $this->assertEquals([4, 3, 2, 1], $paths[0]->toArray(true));
        $this->assertEquals([4, 3, 5, 2, 1], $paths[1]->toArray(true));
        $this->assertEquals([4, 6, 5, 2, 1], $paths[2]->toArray(true));

        $paths = $graph->traverseBackward(4, null, []);
        $this->assertCount(3, $paths);
        $this->assertEquals([4, 3, 2, 1], $paths[0]->toArray(true));
        $this->assertEquals([4, 3, 5, 2, 1], $paths[1]->toArray(true));
        $this->assertEquals([4, 6, 5, 2, 1], $paths[2]->toArray(true));

        $paths = $graph->traverseBackward(4, ['a']);
        $this->assertCount(1, $paths);
        $this->assertEquals([4, 3, 2, 1], $paths[0]->toArray(true));

        $paths = $graph->traverseBackward(4, null, ['b', 'c']);
        $this->assertCount(1, $paths);
        $this->assertEquals([4, 3, 2, 1], $paths[0]->toArray(true));

        $paths = $graph->traverseBackward(4, ['b']);
        $this->assertCount(0, $paths);

        $paths = $graph->traverseBackward(4, null, ['a', 'c']);
        $this->assertCount(0, $paths);

        $paths = $graph->traverseBackward(4, ['c']);
        $this->assertCount(1, $paths);
        $this->assertEquals([4, 6, 5], $paths[0]->toArray(true));

        $paths = $graph->traverseBackward(4, null, ['a', 'b']);
        $this->assertCount(1, $paths);
        $this->assertEquals([4, 6, 5], $paths[0]->toArray(true));

        $paths = $graph->traverseBackward(4, ['a', 'b']);
        $this->assertCount(2, $paths);
        $this->assertEquals([4, 3, 2, 1], $paths[0]->toArray(true));
        $this->assertEquals([4, 3, 5, 2, 1], $paths[1]->toArray(true));

        $paths = $graph->traverseBackward(4, null, ['c']);
        $this->assertCount(2, $paths);
        $this->assertEquals([4, 3, 2, 1], $paths[0]->toArray(true));
        $this->assertEquals([4, 3, 5, 2, 1], $paths[1]->toArray(true));

        $paths = $graph->traverseBackward(4, ['b', 'c']);
        $this->assertCount(1, $paths);
        $this->assertEquals([4, 6, 5, 2], $paths[0]->toArray(true));

        $paths = $graph->traverseBackward(4, null, ['a']);
        $this->assertCount(1, $paths);
        $this->assertEquals([4, 6, 5, 2], $paths[0]->toArray(true));

        $paths = $graph->traverseBackward(4, ['a', 'c']);
        $this->assertCount(2, $paths);
        $this->assertEquals([4, 3, 2, 1], $paths[0]->toArray(true));
        $this->assertEquals([4, 6, 5], $paths[1]->toArray(true));

        $paths = $graph->traverseBackward(4, null, ['b']);
        $this->assertCount(2, $paths);
        $this->assertEquals([4, 3, 2, 1], $paths[0]->toArray(true));
        $this->assertEquals([4, 6, 5], $paths[1]->toArray(true));

        $result = [];
        $graph->traverseBackward(4, null, null, null, true, function(GraphLink $link, array $traveledPath) use (&$result) {
            $data = $link->toArray(true);
            $data[] = count($traveledPath);
            $result[] = $data;
        });

        $this->assertEquals([
            [4, 3, 'a', 0],
            [3, 2, 'a', 1],
            [2, 1, 'a', 2],
            [3, 5, 'b', 1],
            [5, 2, 'b', 2],
            [2, 1, 'a', 3],
            [4, 6, 'c', 0],
            [6, 5, 'c', 1],
            [5, 2, 'b', 2],
            [2, 1, 'a', 3],
        ], $result);

        /* ================ */
        /* TRAVERSE FORWARD */
        /* ================ */

        $paths = $graph->traverseForward(1);
        $this->assertCount(3, $paths);
        $this->assertEquals([1, 2, 3, 4], $paths[0]->toArray(true));
        $this->assertEquals([1, 2, 5, 3, 4], $paths[1]->toArray(true));
        $this->assertEquals([1, 2, 5, 6, 4], $paths[2]->toArray(true));

        $paths = $graph->traverseForward(1, ['a', 'b', 'c']);
        $this->assertCount(3, $paths);
        $this->assertEquals([1, 2, 3, 4], $paths[0]->toArray(true));
        $this->assertEquals([1, 2, 5, 3, 4], $paths[1]->toArray(true));
        $this->assertEquals([1, 2, 5, 6, 4], $paths[2]->toArray(true));

        $paths = $graph->traverseForward(1, null, []);
        $this->assertCount(3, $paths);
        $this->assertEquals([1, 2, 3, 4], $paths[0]->toArray(true));
        $this->assertEquals([1, 2, 5, 3, 4], $paths[1]->toArray(true));
        $this->assertEquals([1, 2, 5, 6, 4], $paths[2]->toArray(true));

        $paths = $graph->traverseForward(1, ['a']);
        $this->assertCount(1, $paths);
        $this->assertEquals([1, 2, 3, 4], $paths[0]->toArray(true));

        $paths = $graph->traverseForward(1, null, ['b', 'c']);
        $this->assertCount(1, $paths);
        $this->assertEquals([1, 2, 3, 4], $paths[0]->toArray(true));

        $paths = $graph->traverseForward(1, ['b']);
        $this->assertCount(0, $paths);

        $paths = $graph->traverseForward(1, null, ['a', 'c']);
        $this->assertCount(0, $paths);

        $paths = $graph->traverseForward(1, ['c']);
        $this->assertCount(0, $paths);

        $paths = $graph->traverseForward(1, null, ['a', 'b']);
        $this->assertCount(0, $paths);

        $paths = $graph->traverseForward(1, ['a', 'b']);
        $this->assertCount(2, $paths);
        $this->assertEquals([1, 2, 3, 4], $paths[0]->toArray(true));
        $this->assertEquals([1, 2, 5, 3, 4], $paths[1]->toArray(true));

        $paths = $graph->traverseForward(1, null, ['c']);
        $this->assertCount(2, $paths);
        $this->assertEquals([1, 2, 3, 4], $paths[0]->toArray(true));
        $this->assertEquals([1, 2, 5, 3, 4], $paths[1]->toArray(true));

        $paths = $graph->traverseForward(1, ['a', 'c']);
        $this->assertCount(1, $paths);
        $this->assertEquals([1, 2, 3, 4], $paths[0]->toArray(true));

        $paths = $graph->traverseForward(1, null, ['b']);
        $this->assertCount(1, $paths);
        $this->assertEquals([1, 2, 3, 4], $paths[0]->toArray(true));

        $paths = $graph->traverseForward(1, ['b', 'c']);
        $this->assertCount(0, $paths);

        $result = [];
        $graph->traverseForward(1, null, null, null, true, function(GraphLink $link, array $traveledPath) use (&$result) {
            $data = $link->toArray(true);
            $data[] = count($traveledPath);
            $result[] = $data;
        });

        $this->assertEquals([
            [1, 2, 'a', 0],
            [2, 3, 'a', 1],
            [3, 4, 'a', 2],
            [2, 5, 'b', 1],
            [5, 3, 'b', 2],
            [3, 4, 'a', 3],
            [5, 6, 'c', 2],
            [6, 4, 'c', 3],
        ], $result);

        $paths = $graph->traverseForward(1, ['a', 'b'], null, 3);
        $this->assertCount(2, $paths);
        $this->assertEquals([1, 2, 3], $paths[0]->toArray(true));
        $this->assertEquals([1, 2, 5], $paths[1]->toArray(true));

        /* ================== */
        /* REVERSE CLONE TEST */
        /* ================== */

        $paths = $graph->traverseForward(1, null, ['b']);
        $path1 = $paths[0];
        $this->assertEquals([1, 2, 3, 4], $path1->toArray(true));

        $path1->reverse();
        $this->assertEquals([4, 3, 2, 1], $path1->toArray(true));

        $path2 = $path1->reverse(true);
        $this->assertEquals([4, 3, 2, 1], $path1->toArray(true));
        $this->assertEquals([1, 2, 3, 4], $path2->toArray(true));

        // loop test
        $graph->link(3, 1, 'a');

        $paths = $graph->traverseForward(1);
        $this->assertCount(5, $paths);
        $this->assertEquals([1, 2, 3, 4], $paths[0]->toArray(true));
        $this->assertEquals([1, 2, 3, 1], $paths[1]->toArray(true));
        $this->assertEquals([1, 2, 5, 3, 4], $paths[2]->toArray(true));
        $this->assertEquals([1, 2, 5, 3, 1], $paths[3]->toArray(true));
        $this->assertEquals([1, 2, 5, 6, 4], $paths[4]->toArray(true));

        $paths = $graph->traverseBackward(4);
        $this->assertCount(4, $paths);
        $this->assertEquals([4, 3, 2, 1, 3], $paths[0]->toArray(true));
        $this->assertEquals([4, 3, 5, 2, 1, 3], $paths[1]->toArray(true));
        $this->assertEquals([4, 6, 5, 2, 1, 3, 2], $paths[2]->toArray(true));
        $this->assertEquals([4, 6, 5, 2, 1, 3, 5], $paths[3]->toArray(true));

        $graph = new Graph(
            [1 => 11, 2 => 22, 3 => 33],
            [[1, 2, 'a'], [2, 3, 'a'], [3, 1, 'a'], [3, 2, 'a'], [2, 1, 'a'], [1, 3, 'a']]
        );

        $paths = $graph->traverseForward(1);
        $this->assertCount(6, $paths);
        $this->assertEquals([1, 2, 3, 1], $paths[0]->toArray(true));
        $this->assertEquals([1, 2, 3, 2], $paths[1]->toArray(true));
        $this->assertEquals([1, 2, 1], $paths[2]->toArray(true));
        $this->assertEquals([1, 3, 1], $paths[3]->toArray(true));
        $this->assertEquals([1, 3, 2, 3], $paths[4]->toArray(true));
        $this->assertEquals([1, 3, 2, 1], $paths[5]->toArray(true));

        $paths = $graph->traverseBackward(1);
        $this->assertCount(6, $paths);
        $this->assertEquals([1, 3, 2, 1], $paths[0]->toArray(true));
        $this->assertEquals([1, 3, 2, 3], $paths[1]->toArray(true));
        $this->assertEquals([1, 3, 1], $paths[2]->toArray(true));
        $this->assertEquals([1, 2, 1], $paths[3]->toArray(true));
        $this->assertEquals([1, 2, 3, 2], $paths[4]->toArray(true));
        $this->assertEquals([1, 2, 3, 1], $paths[5]->toArray(true));
    }

    /**
     * @throws LinkedListException
     * @throws MappedCollectionException
     * @throws MappedLinkedListException
     */
    public function testCloneObjects()
    {
        /*
         * MappedCollection
         */
        $mc = new MappedCollection(['a1' => 1, 'a2' => 2, 'a3' => 3]);
        $mcCopy = clone $mc;

        $this->assertTrue($mc->getMap() === $mcCopy->getMap());

        $mc->add('a4', 4);
        $this->assertEquals(['a1' => 1, 'a2' => 2, 'a3' => 3, 'a4' => 4], $mc->getMap());
        $this->assertEquals(['a1' => 1, 'a2' => 2, 'a3' => 3], $mcCopy->getMap());

        $mc->delete('a2');
        $mcCopy->delete('a3');

        $this->assertEquals(['a1' => 1, 'a3' => 3, 'a4' => 4], $mc->getMap());
        $this->assertEquals(['a1' => 1, 'a2' => 2], $mcCopy->getMap());

        $mc = new MappedCollection(['a1' => 1, 'a2' => 2, 'a3' => 3]);
        $mcCopy = clone $mc;

        $mcCopy->add('a4', 4);
        $this->assertEquals(['a1' => 1, 'a2' => 2, 'a3' => 3], $mc->getMap());
        $this->assertEquals(['a1' => 1, 'a2' => 2, 'a3' => 3, 'a4' => 4], $mcCopy->getMap());

        $mc->delete('a2');
        $mcCopy->delete('a3');

        $this->assertEquals(['a1' => 1, 'a3' => 3], $mc->getMap());
        $this->assertEquals(['a1' => 1, 'a2' => 2, 'a4' => 4], $mcCopy->getMap());

        /*
         * LinkedList
         */
        $ll = new LinkedList([1, 2, 3]);
        $llCopy = clone $ll;

        $this->assertTrue($ll->getFirst() !== $llCopy->getFirst());
        $this->assertTrue($ll->getLast() !== $llCopy->getLast());

        $ll->pushBack(4);
        $this->assertEquals([1, 2, 3, 4], $ll->toArray());
        $this->assertEquals([1, 2, 3], $llCopy->toArray());

        $ll->popFront();
        $llCopy->popBack();

        $this->assertEquals([2, 3, 4], $ll->toArray());
        $this->assertEquals([1, 2], $llCopy->toArray());

        $ll = new LinkedList([1, 2, 3]);
        $llCopy = clone $ll;

        $llCopy->pushBack(4);
        $this->assertEquals([1, 2, 3], $ll->toArray());
        $this->assertEquals([1, 2, 3, 4], $llCopy->toArray());

        $ll->popFront();
        $llCopy->popBack();

        $this->assertEquals([2, 3], $ll->toArray());
        $this->assertEquals([1, 2, 3], $llCopy->toArray());

        /*
         * MappedLinkedList
         */
        $mll = new MappedLinkedList(['a' => 1, 'b' => 2]);
        $mllCopy = clone $mll;

        $this->assertTrue($mll->getPositionsMap() !== $mllCopy->getPositionsMap());
        $this->assertTrue($mll->getList() !== $mllCopy->getList());
        $this->assertTrue($mll->getList()->getFirst() !== $mllCopy->getList()->getFirst());
        $this->assertTrue($mll->getList()->getFirst() !== $mllCopy->getList()->getFirst());

        $mll->pushBack('c', 3);
        $mll->popFront();

        $this->assertEquals(['b' => 2, 'c' => 3], $mll->toArray());
        $this->assertEquals(['a' => 1, 'b' => 2], $mllCopy->toArray());

        $mll = new MappedLinkedList(['a' => 1, 'b' => 2]);
        $mllCopy = clone $mll;

        $mllCopy->pushBack('c', 3);
        $mllCopy->popFront();

        $this->assertEquals(['a' => 1, 'b' => 2], $mll->toArray());
        $this->assertEquals(['b' => 2, 'c' => 3], $mllCopy->toArray());

        /*
         * SortedLinkedList
         */
        $sll = new IntegerSortedLinkedList([3, 1, 2]);
        $sllCopy = clone $sll;

        $this->assertTrue($sll->getList() !== $sllCopy->getList());

        $sll->insert(2);
        $sll->popBack();

        $this->assertEquals([1, 2, 2], $sll->toArray());
        $this->assertEquals([1, 2, 3], $sllCopy->toArray());

        $sll = new IntegerSortedLinkedList([3, 1, 2]);
        $sllCopy = clone $sll;

        $sllCopy->insert(2);
        $sllCopy->popBack();

        $this->assertEquals([1, 2, 3], $sll->toArray());
        $this->assertEquals([1, 2, 2], $sllCopy->toArray());

        /*
         * SortedMappedLinkedList
         */
        $smll = new ArraySortedMappedLinkedList(['a' => ['id' => 'a'], 'c' => ['id' => 'c']]);
        $smllCopy = clone $smll;

        $this->assertTrue($smll->getList() !== $smllCopy->getList());

        $smll->insert('b', ['id' => 'b']);
        $smll->popBack();

        $this->assertEquals(['a' => ['id' => 'a'], 'b' => ['id' => 'b']], $smll->toArray());
        $this->assertEquals(['a' => ['id' => 'a'], 'c' => ['id' => 'c']], $smllCopy->toArray());

        $smll = new ArraySortedMappedLinkedList(['a' => ['id' => 'a'], 'c' => ['id' => 'c']]);
        $smllCopy = clone $smll;

        $smllCopy->insert('b', ['id' => 'b']);
        $smllCopy->popBack();

        $this->assertEquals(['a' => ['id' => 'a'], 'c' => ['id' => 'c']], $smll->toArray());
        $this->assertEquals(['a' => ['id' => 'a'], 'b' => ['id' => 'b']], $smllCopy->toArray());
    }

    /**
     * @param array $source
     * @param string $columnName
     * @return array
     */
    protected function getColumn(array $source, string $columnName): array
    {
        $result = [];

        foreach($source as $val) {
            $result[] = $val[$columnName];
        }

        return $result;
    }

    /**
     * Debug print method
     * @param mixed $log
     */
    protected function log($log)
    {
        $output = new Output([]);
        $output->writeln(PHP_EOL);
        $output->writeln('-------------------');
        $output->writeln(print_r($log, 1));
        $output->writeln('-------------------');
    }
}