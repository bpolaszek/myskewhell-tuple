<?php
namespace MySkewHell\Tuple;


class TupleStringWrapper extends TupleConditionnalWrapper implements TupleInterface, \Countable {

    protected   $string =   '';

    /**
     * @param string $string
     */
    public function __construct($string = '') {
        $this->string   =   $string;
    }

    /**
     * @return string
     */
    public function __toString() {
        return (string) $this->string;
    }

    /**
     * @return int
     */
    public function count() {
        return 1;
    }

}