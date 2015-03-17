<?php
namespace MySkewHell\Tuple;


class TupleANDWrapper extends TupleConditionnalWrapper implements TupleInterface {

    /**
     * String context
     * @return string
     */
    public function __toString() {
        return (string) sprintf('(%s)', implode(PHP_EOL . 'AND ', array_map(function ($tuple) { return (string) $tuple; }, $this->getTuples())));
    }

} 