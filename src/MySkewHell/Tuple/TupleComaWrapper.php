<?php
/**
 * TupleComaWrapper.php
 * Generated by PhpStorm - 08/2015
 * Project myskewhell-tuple
 * @author Beno!t POLASZEK
 **/

namespace MySkewHell\Tuple;

class TupleComaWrapper extends TupleConditionnalWrapper implements TupleInterface {

    /**
     * String context
     * @return string
     */
    public function __toString() {
        return (string) sprintf('%s', implode(',' . PHP_EOL, array_map(function ($tuple) { return (string) $tuple; }, $this->getTuples())));
    }

}