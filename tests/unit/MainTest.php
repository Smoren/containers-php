<?php


namespace Smoren\Containers\Tests\Unit;

use Codeception\Lib\Console\Output;
use Codeception\Test\Unit;
use Exception;
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