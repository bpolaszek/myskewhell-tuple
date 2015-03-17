<?php
namespace MySkewHell\Tuple;


interface TupleInterface {

    /**
     * Transforms the tuple to a string
     * @return string
     */
    public function __toString();

    /**
     * Returns an array of values to bind
     * @return array
     */
    public function getValues();

    /**
     * Returns the values, separated by a comma
     * @return mixed
     */
    public function getValuesAsString();

    /**
     * Returns an array of placeholders
     * @return array
     */
    public function getPlaceHolders();

    /**
     * Returns the placeholders, separated by a comma
     * @return string
     */
    public function getPlaceHoldersAsString();

    /**
     * Getter / Setter
     * Use named or positional placeholders ?
     * @param null $value
     * @return bool|$this
     * @link http://php.net/manual/en/pdo.prepared-statements.php
     */
    public function useNamedPlaceHolders($value = null);

} 