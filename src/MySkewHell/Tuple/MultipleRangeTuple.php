<?php
namespace MySkewHell\Tuple;


class MultipleRangeTuple extends Tuple {

    public static    $VALID_OPERATORS    =   ['IN', 'NOT IN'];

    /**
     * @param array $tuple
     */
    public function __construct(array $tuple) {
        if (!$this->ValidateArray($tuple))
            throw new TupleException("Invalid tuple " . print_r($tuple, true));

        $this->setField($tuple[0]);
        $this->setOperator($tuple[1]);
        $this->setValues((array) $tuple[2]);
    }

    /**
     * @see Tuple::getPlaceHolders()
     * @return array
     */
    public function getPlaceHolders() {
        if ($this->useNamedPlaceHolders() && count($this->values) > 1) {
            $placeHolders   =   [];
            for ($i = 1; $i <= count($this->values); $i++)
                $placeHolders[] =   sprintf(':%s%d', $this->getField(false), $i);
            return $placeHolders;
        }
        return parent::getPlaceHolders();
    }

    /**
     * String context
     * @return string
     */
    public function getValuesAsString() {
        if (!is_array($this->getValues()))
            return sprintf('(%s)', $this->getValues());
        return sprintf('(%s)', implode(', ', $this->getValues()));
    }

    /**
     * String context
     * @return string
     */
    public function getPlaceHoldersAsString() {
        if (!is_array($this->getPlaceHolders()))
            return sprintf('(%s)', $this->getPlaceHolders());
        return sprintf('(%s)', implode(', ', $this->getPlaceHolders()));
    }

    /**
     * @param $array
     * @return bool
     */
    public static function ValidateArray($array) {
        return (bool) (static::IsAnIndexedArray($array) && count($array) == 3 && (!is_array($array[2]) || (is_array($array[2]) && count($array[2]) >= 1)));
    }

} 