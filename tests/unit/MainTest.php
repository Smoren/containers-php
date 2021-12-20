<?php


namespace Smoren\Structs\tests\unit;

use Codeception\Lib\Console\Output;
use Codeception\Test\Unit;
use Smoren\Structs\exceptions\LinkedListException;
use Smoren\Structs\exceptions\MappedCollectionException;
use Smoren\Structs\structs\LinkedList;
use Smoren\Structs\structs\LinkedListItem;
use Smoren\Structs\structs\MappedCollection;
use Smoren\Structs\structs\MappedLinkedList;

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
     * @throws \Exception
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

        $ll->pop($ll->getFirst()->getNext()->getNext());
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

        $ll = new LinkedList(['a1', 'a2', 'a3']);
        $this->assertEquals(['a1', 'a2', 'a3'], $ll->toArray());
        $this->assertEquals(3, $ll->count());
    }

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

        $this->assertEquals(1, $ll->pop(101)->getData());
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

        $ll->clear();
        $this->assertEquals([], $ll->toArray());
        $this->assertEquals(0, $ll->count());

        $ll = new MappedLinkedList(['a1' => 'b1', 'a2' => 'b2', 'a3' => 'b3']);
        $this->assertEquals(['a1', 'a2', 'a3'], array_keys($ll->toArray()));
        $this->assertEquals(['b1', 'b2', 'b3'], array_values($ll->toArray()));
        $this->assertEquals(3, $ll->count());

        // TODO try checks
    }

    /**
     * Debug print method
     * @param mixed $log
     */
    public function log($log)
    {
        $output = new Output([]);
        $output->writeln(PHP_EOL);
        $output->writeln('-------------------');
        $output->writeln(print_r($log, 1));
        $output->writeln('-------------------');
    }
}