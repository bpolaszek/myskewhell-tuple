<?php
namespace MySkewHell\Tuple;


class TupleORWrapper extends TupleConditionnalWrapper implements TupleInterface {

    /**
     * String context
     * @return string
     */
    public function __toString() {
        return (string) sprintf('(%s)', implode(PHP_EOL . 'OR ', array_map(function ($tuple) { return (string) $tuple; }, $this->getTuples())));
    }

} 