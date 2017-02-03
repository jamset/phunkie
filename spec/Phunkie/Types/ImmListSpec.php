<?php

namespace spec\Phunkie\Types;

use Phunkie\Cats\Show;
use function Phunkie\Functions\show\showValue;
use function Phunkie\Functions\show\usesTrait;
use Phunkie\Ops\ImmList\ImmListApplicativeOps;
use Phunkie\Types\Cons;
use Phunkie\Types\Nil;
use PhpSpec\ObjectBehavior;

use Eris\TestTrait;
use Eris\Generator\SequenceGenerator as SeqGen;
use Eris\Generator\IntegerGenerator as IntGen;

/**
 * @mixin ImmListApplicativeOps
 */
class ImmListSpec extends ObjectBehavior
{
    use TestTrait;

    function let()
    {
        $this->beAnInstanceOf(Cons::class);
        $this->beConstructedWith(1,ImmList(2,3));
    }

    function it_is_showable()
    {
        $this->shouldBeShowable();
        expect(showValue(ImmList(1,2,3)))->toReturn("List(1, 2, 3)");
    }

    function it_is_a_functor()
    {
        $spec = $this;
        $this->forAll(
            new SeqGen(new IntGen())
        )->then(function($list) use ($spec) {
            expect(ImmList(...$list)->map(function ($x) {
                return $x + 1;
            }))->toBeLike(ImmList(...array_map(function($x) { return $x + 1; }, $list)));
        });
    }

    function it_returns_an_empty_list_when_an_empty_list_is_mapped()
    {
        $this->beAnInstanceOf(Nil::class);
        $this->beConstructedWith();
        $this->map(function($x) { return $x + 1; })->shouldBeEmpty();
    }

    function it_is_has_applicative_ops()
    {
        expect(usesTrait($this->getWrappedObject(), ImmListApplicativeOps::class))->toBe(true);
    }

    function it_returns_an_empty_list_when_an_empty_list_is_applied()
    {
        $this->beAnInstanceOf(Nil::class);
        $this->beConstructedWith();
        $this->apply(ImmList(function($x) { return $x + 1; }))->shouldBeEmpty();
    }

    function it_applies_the_result_of_the_function_to_a_List()
    {
        $spec = $this;
        $this->forAll(
            new SeqGen(new IntGen())
        )->then(function($list) use ($spec) {
            expect(ImmList(...$list)->apply(ImmList(function($x) { return $x + 1; })))
                ->toBeLike(ImmList(...array_map(function($x) { return $x + 1; }, $list)));
        });
    }

    function it_returns_its_length()
    {
        $this->isAListContaining(1,2,3);
        $this->length->shouldBe(3);
    }

    function it_has_filter()
    {
        $this->isAListContaining(1,2,3);
        $this->filter(function($x){return $x == 2;})->shouldBeLike(ImmList(2));
    }

    function it_has_reject()
    {
        $this->isAListContaining(1,2,3);
        $this->reject(function($x){return $x == 2;})->shouldBeLike(ImmList(1, 3));
    }

    function it_implements_reduce()
    {
        $this->isAListContaining(1,2,3);
        $this->reduce(function($x, $y){return $x  + $y;})->shouldBe(6);
    }

    function it_implements_reduce_string_example()
    {
        $this->isAListContaining("a", "b", "c");
        $this->reduce(function($x, $y){return $x  . $y;})->shouldBe("abc");
    }

    function it_will_complain_if_reduce_returns_a_type_different_to_the_list_type()
    {
        $this->isAListContaining(1,2,3);
        $this->shouldThrow()->duringReduce(function($x, $y){return "Oh no! a string!";});
    }

    function it_can_be_casted_to_array()
    {
        $this->isAListContaining(1,2,3);
        $this->toArray()->shouldBe([1,2,3]);
    }

    function it_zips()
    {
        $this->isAListContaining(1,2,3);
        $this->zip(ImmList("A", "B", "C"))->shouldBeLike(
            ImmList(Pair(1,"A"), Pair(2,"B"), Pair(3,"C"))
        );
    }

    function it_takes_n_elements_from_list()
    {
        $this->isAListContaining(1,2,3);
        $this->take(2)->shouldBeLike(ImmList(1,2));
    }

    function it_drops_n_elements_from_list()
    {
        $this->isAListContaining(1,2,3);
        $this->drop(2)->shouldBeLike(ImmList(3));
    }

    function it_implements_head()
    {
        $this->isAListContaining(1,2,3);
        $this->head->shouldBe(1);
    }

    function it_implements_tail()
    {
        $this->isAListContaining(1,2,3);
        $this->tail->shouldBeLike(ImmList(2,3));
    }

    function it_implements_init()
    {
        $this->isAListContaining(1,2,3);
        $this->init->shouldBeLike(ImmList(1,2));
    }

    function it_implements_last()
    {
        $this->isAListContaining(1,2,3);
        $this->last->shouldBe(3);
    }

    function it_implements_shortcut_for_mapping_over_class_members()
    {
        $_ = underscore();
        $this->isAListContaining(new User("John"), new User("Alice"));
        $this->map($_->name)->map("strtoupper")->shouldBeLike(ImmList("JOHN", "ALICE"));
    }

    function getMatchers()
    {
        return ["beShowable" => function($sus){
            return usesTrait($sus, Show::class);
        }];
    }

    private function isAListContaining($x, ...$xs)
    {
        $this->beAnInstanceOf(Cons::class);
        $this->beConstructedWith($x, ImmList(...$xs));
    }
}

class User {
    public $name;
    public function __construct($name)
    {
        $this->name = $name;
    }
}