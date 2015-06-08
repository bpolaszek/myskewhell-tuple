<?php
namespace MySkewHell\Tuple;


class SimpleRangeTuple extends Tuple {

    public static    $VALID_OPERATORS    =   ['BETWEEN'];

    /**
     * @param array $tuple
     */
    public function __construct(array $tuple) {
        if (!$this->ValidateArray($tuple))
            throw new TupleException("Invalid tuple " . print_r($tuple, true));

        if (is_array($tuple[2]))
            $values =   $tuple[2];

        else
            $values =   [$tuple[2]];

        $this->setField($tuple[0]);
        $this->setOperator($tuple[1]);
        $this->setValues($values);
    }

    /**
     * @see Tuple::getPlaceHolders()
     * @return array
     */
    public function getPlaceHolders() {
        if ($this->useNamedPlaceHolders() && count($this->values) > 1) {
            $placeHolders   =   [];
            for ($i = 1; $i <= count($this->values); $i++)
                $placeHolders[] =   sprintf(':%s%d', $this->escapeNamedPlaceHolder($this->getField(false)), $i);
            return $placeHolders;
        }
        return parent::getPlaceHolders();
    }

    /**
     * String context
     * @return string
     */
    public function getPlaceholdersAsString() {
        return implode(' AND ', $this->getPlaceHolders());
    }

    /**
     * String context
     * @return string
     */
    public function getValuesAsString() {
        return implode(' AND ', $this->getValues());
    }

    /**
     * @param $array
     * @return bool
     */
    public static function ValidateArray($array) {
        return (bool) (static::IsAnIndexedArray($array) && count($array) == 3 && is_array($array[2]) && count($array[2]) === 2);
    }
} 