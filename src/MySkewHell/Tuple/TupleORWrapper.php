<?php
namespace MySkewHell\Tuple;


class TupleORWrapper extends TupleConditionnalWrapper implements TupleInterface {

    /**
     * Loads a mixed input to a TupleInterface object and adds it
     * @param mixed $input
     * @return $this
     */
    public function loadTuple($input) {
        if (static::IsAnArrayOfTupleInterface($input))
            return $this->setTuples($input);

        elseif ($input instanceof TupleInterface)
            return $this->addTuple($input);

        elseif ((is_array($input) || is_string($input)) && TupleFactory::IsTuplable($input))
            return $this->addTuple(TupleFactory::LoadTuple($input, false));
    }

    /**
     * String context
     * @return string
     */
    public function __toString() {
        return (string) sprintf('(%s)', implode(PHP_EOL . 'OR ', array_map(function ($tuple) { return (string) $tuple; }, $this->getTuples())));
    }

} 