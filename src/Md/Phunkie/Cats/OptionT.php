<?php

namespace Md\Phunkie\Cats;

use Md\Phunkie\Types\Option;

/**
 * OptionT<F, A>
 */
class OptionT
{
    /**
     * @var F<Option<A>>
     */
    private $value;

    public function __construct($value)
    {
        $this->value = $value;
    }

    /**
     * @param callable<A, B> $f
     * @return OptionT<F, B>
     */
    public function map($f): OptionT
    {
        return OptionT($this->value->map(function(Option $o)  use ($f) { return $o->map($f); }));
    }

    /**
     * @param callable<A, OptionT<F, B>> $f
     * @return OptionT<F, B>
     */
    public function flatMap($f): OptionT
    {
        return OptionT($this->value->flatMap(function(Option $o) use ($f) {
            return $o->map(
                function($a) use ($f) {
                    return $f($a)->value;
                }
            )->getOrElse($this->value->pure(None()));
        }));
    }

    /**
     * @return F<Boolean>
     */
    public function isDefined()
    {
        return $this->value->map(function(Option $o) { return $o->isDefined(); });
    }

    /**
     * @return F<Boolean>
     */
    public function isEmpty()
    {
        return $this->value->map(function(Option $o) { return $o->isEmpty(); });
    }

    /**
     * @param A $default
     * @return F<A>
     */
    public function getOrElse($default)
    {
        return $this->value->map(function(Option $o) use ($default) { return $o->getOrElse($default); });
    }
}