<?php
namespace MySkewHell\Tuple;


class ComparisonTuple extends Tuple {

    public static    $VALID_OPERATORS    =   ['=', '!=', '<>', '>', '<', '>=', '<=', 'LIKE'];

    /**
     * @param array $tuple
     */
    public function __construct(Array $tuple) {
        if (!$this->ValidateArray($tuple))
            throw new TupleException("Invalid tuple " . print_r($tuple, true));

        $this->setField($tuple[0]);
        $this->setOperator($tuple[1]);
        $this->setValues((array) $tuple[2]);
    }

    /**
     * @param $array
     * @return bool
     */
    public static function ValidateArray($array) {
        return (bool) (static::IsAnIndexedArray($array) && count($array) == 3 && (!is_array($array[2]) || count($array[2]) === 1));
    }

} 