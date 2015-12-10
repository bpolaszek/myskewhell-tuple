<?php
namespace MySkewHell\Tuple;


use Traversable;

abstract class TupleConditionnalWrapper implements \ArrayAccess, \Countable, TupleInterface, \IteratorAggregate {

    /**
     * @var TupleInterface[]
     */
    protected   $tuples             =   [];
    protected   $namedPlaceHolders  =   false;
    protected   $wrapFields         =   true;

    public function __construct($input = null) {
        if (!is_null($input))
            $this->loadTuple($input);
    }

    /**
     * Loads a mixed input to a TupleInterface object and adds it
     * @param mixed $input
     * @return $this
     */
    public function loadTuple($input) {
        if (static::IsAnArrayOfTupleInterface($input)) {
            foreach ($input AS $tuple)
                $tuple->useNamedPlaceHolders($this->useNamedPlaceHolders())->wrapFields($this->wrapFields());
            return $this->addTuples($input);
        }

        elseif ($input instanceof TupleInterface) {
            $input->useNamedPlaceHolders($this->useNamedPlaceHolders())->wrapFields($this->wrapFields());
            return $this->addTuple($input);
        }

        elseif ((is_array($input) || is_string($input)) && TupleFactory::IsTuplable($input)) {
            $tuple = TupleFactory::LoadTuple($input);
            $tuple->useNamedPlaceHolders($this->useNamedPlaceHolders())->wrapFields($this->wrapFields());
            return $this->addTuple($tuple);
        }
        return $this;
    }

    /**
     * Adds a TupleInterface object to the wrapper
     * @param TupleInterface $tuple
     * @return $this
     */
    public function addTuple(TupleInterface $tuple) {
        $this->tuples[] =   $tuple;
        return $this;
    }

    /**
     * @param array $tuples
     * @return $this
     */
    public function addTuples(array $tuples) {
        foreach ($tuples AS $tuple)
            $this->addTuple($tuple);
        return $this;
    }

    /**
     * @param array $tuples
     * @return $this
     * @throws TupleException
     */
    public function setTuples(array $tuples) {

        if (!static::IsAnArrayOfTupleInterface($tuples))
            throw new TupleException("This is not an array of TupleInterface");

        $this->tuples = array_filter($tuples, function($tuple) { return ($tuple instanceof TupleInterface); });
        return $this;
    }

    /**
     * @return \MySkewHell\Tuple\TupleInterface[]
     */
    public function getTuples() {
        return $this->tuples;
    }

    /**
     * @return array
     */
    public function getValues() {
        $values   =   [];
        foreach ($this->getTuples() AS $tuple)
            $values   =   array_merge($values, (array) $tuple->getValues());
        return $values;
    }

    /**
     * @return string
     */
    public function getValuesAsString() {
        return implode(', ', $this->getValues());
    }

    /**
     * @return array
     */
    public function getPlaceHolders() {
        $placeHolders   =   [];
        foreach ($this->getTuples() AS $tuple)
            $placeHolders   =   array_merge($placeHolders, (array) $tuple->getPlaceHolders());
        return $placeHolders;
    }

    /**
     * @return string
     */
    public function getPlaceHoldersAsString() {
        return implode(', ', $this->getPlaceHolders());
    }

    /**
     * @return string
     */
    public function simulateSql() {
        $preview      =   (string) $this;

        if (preg_match('#:([a-zA-Z0-9_]+)#', $preview))
            $preview = preg_replace('#:([a-zA-Z0-9_]+)#', '?', $preview);

        $nbPlaceHolders     =   substr_count($preview, '?');

        if (count($this->getValues()) === $nbPlaceHolders)
            $preview  =    vsprintf(str_replace('?', '%s', $preview), array_map(function ($value) {

                if (is_null($value))
                    return 'NULL';

                elseif (is_bool($value))
                    return (int) $value;

                elseif (is_numeric($value))
                    return $value;

                else
                    return (string) "'". addslashes($value) . "'";

            }, $this->getValues()));

        return str_replace('= NULL', 'IS NULL', $preview);
    }

    /**
     * @param $value
     * @return $this
     */
    public function useNamedPlaceHolders($value = null) {
        if (is_null($value))
            return $this->namedPlaceHolders;

        $this->namedPlaceHolders    =   (bool) $value;
        foreach ($this->getTuples() As $tuple)
            $tuple->useNamedPlaceHolders($value);

        return $this;
    }

    /**
     * @param $value
     * @return $this
     */
    public function wrapFields($value = null) {
        if (is_null($value))
            return $this->wrapFields;

        $this->wrapFields    =   (bool) $value;
        foreach ($this->getTuples() As $tuple)
            $tuple->wrapFields($value);

        return $this;
    }

    /**
     * Retrieve an external iterator
     * @link http://php.net/manual/en/iteratoraggregate.getiterator.php
     * @return Traversable An instance of an object implementing <b>Iterator</b> or
     * <b>Traversable</b>
     */
    public function getIterator() {
        return new \ArrayIterator($this->getValues());
    }

    /**
     * Checks if the array contains TupleInterface
     * @param $input
     * @return bool
     */
    public static function IsAnArrayOfTupleInterface($input) {
        return $input === array_filter((array) $input, function($tuple) { return ($tuple instanceof TupleInterface); });
    }

    /**
     * Checks if the array contains TupleInterface
     * @param $input
     * @return bool
     */
    public static function IsAnArrayOfTupable(Array $input) {
        return count($input) === count(array_map(function ($t) { return $t instanceof TupleInterface || (is_array($t) && TupleFactory::IsTuplable($t)); }, $input));
    }

    /**
     * ArrayAccess interface implementation
     * @param mixed $offset
     * @return bool
     */
    public function offsetExists($offset) {
        return array_key_exists($offset, $this->getTuples());
    }

    /**
     * ArrayAccess interface implementation
     * @param mixed $offset
     */
    public function offsetUnset($offset) {
        unset($this->tuples[$offset]);
    }

    /**
     * ArrayAccess interface implementation
     * @param mixed $offset
     * @return mixed|TupleInterface|null
     */
    public function offsetGet($offset) {
        return ($this->offsetExists($offset)) ? $this->tuples[$offset] : null;
    }

    /**
     * ArrayAccess interface implementation
     * @param mixed $offset
     * @param mixed $value
     * @return $this|void
     */
    public function offsetSet($offset, $value) {
        $this->loadTuple($value);
    }

    public function count() {
        return count($this->tuples);
    }

} 