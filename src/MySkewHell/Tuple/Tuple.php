<?php
namespace MySkewHell\Tuple;

use Traversable;

abstract class Tuple implements TupleInterface, \IteratorAggregate {

    protected           $namedPlaceHolders  =   false;
    protected           $field              =   '';
    protected           $operator           =   '';
    protected           $values             =   [];


    public static       $VALID_OPERATORS    =   [];

    /**
     * @param string $field
     * @return $this - Provides Fluent Interface
     */
    public function setField($field) {
        if (strpos($field, '`') !== false)
            $field  =   str_replace('`', '', $field);
        $this->field = $field;
        return $this;
    }

    /**
     * @param bool $escape
     * @return string
     */
    public function getField($escape = true) {
        return ($escape) ? $this->wrapField($this->field) : $this->field;
    }

    /**
     * @param $operator
     * @return $this
     * @throws TupleException
     */
    public function setOperator($operator) {
        if (!$this->ValidateOperator($operator))
            throw new TupleException("Invalid operator " . $operator);
        $this->operator = $operator;
        return $this;
    }

    /**
     * @return string
     */
    public function getOperator() {
        return $this->operator;
    }

    /**
     * @param array $values
     * @return $this - Provides Fluent Interface
     */
    public function setValues(Array $values) {
        $this->values = $values;
        return $this;
    }

    /**
     * @return array
     */
    public function getValues() {
        if ($this->useNamedPlaceHolders())
            return array_combine(array_map(function($placeHolder) {
                return strpos($placeHolder, ':') === 0 ? substr($placeHolder, 1, strlen($placeHolder) - 1) : $placeHolder;
            }, $this->getPlaceHolders()), $this->values);
        else
            return $this->values;
    }

    /**
     * @param boolean $value
     * @return $this - Provides Fluent Interface
     */
    public function useNamedPlaceHolders($value = null) {
        if (is_null($value))
            return $this->namedPlaceHolders;
        else
            $this->namedPlaceHolders = (bool) $value;
        return $this;
    }

    /**
     * @return array
     */
    public function getPlaceHolders() {
        return ($this->useNamedPlaceHolders()) ?  array_fill(0, count($this->values), sprintf(':%s', $this->escapeNamedPlaceHolder($this->getField(false)))) : array_fill(0, count($this->values), '?');
    }

    /**
     * @return string
     */
    public function getPlaceHoldersAsString() {
        return implode(', ', $this->getPlaceHolders());
    }

    /**
     * String context
     * @return string
     */
    public function getValuesAsString() {
        if (!is_array($this->getValues()))
            return $this->getValues();
        else
            return implode(', ', $this->getValues());
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
     * String context
     * @return string
     */
    public function __toString() {
        return (string) sprintf('%s %s %s', (string) $this->getField(), (string) $this->getOperator(), (string) $this->getPlaceHoldersAsString());
    }

    /**
     * Checks if the array is an indexed array
     * @param $array
     * @return bool
     */
    public static function IsAnIndexedArray($array) {
        if (!is_array($array))
            return false;
        return (bool) (array_values($array) === $array);
    }

    /**
     * Checks if the operator is valid
     * @param $operator
     * @return bool
     */
    public static function ValidateOperator($operator) {
        return in_array(strtolower($operator), array_map('strtolower', static::$VALID_OPERATORS));
    }

    /**
     * Checks if an array is an array of tuples
     * @param $input
     * @return array
     */
    public static function IsAnArrayOfTuples($input) {
        return $input === array_filter($input, function($tuple) { return ($tuple instanceof self); });
    }

    /**
     * @param string $field
     * @return string
     */
    public static function WrapField($field) {
        if (strpos($field, '.') !== false) {
            $field      =   explode('.', $field);
            $field[1]   =   sprintf('`%s`', $field[1]);
            $field      =   implode('.', $field);
            return $field;
        }
        return sprintf('`%s`', $field);
    }

    /**
     * @param $placeHolder
     * @return string
     */
    public static function escapeNamedPlaceHolder($placeHolder) {
        return strpos($placeHolder, '.') !== false ? str_replace('.', '__', $placeHolder) : $placeHolder;
    }

} 