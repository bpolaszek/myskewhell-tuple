<?php
namespace MySkewHell\Tuple;


class TupleFactory {

    /**
     * @param mixed $input
     * @param bool $prefer_and (prefer an AND condition rather than an OR)
     * @return TupleInterface
     * @throws TupleException
     */
    public static function LoadTuple($input, $prefer_and = true) {

        // If it's already a Tuple object
        if ($input instanceof TupleInterface)
            return $input;

        // If it's an array of just a string
        elseif (is_array($input) && Tuple::IsAnIndexedArray($input) && array_key_exists(0, $input) && count($input) === 1 && is_string($input[0]))
            return new TupleStringWrapper($input[0]);

        // If it's just a string
        elseif (is_string($input))
            return new TupleStringWrapper($input);

        // Case of an array of tuples
        elseif (is_array($input) && Tuple::IsAnIndexedArray($input) && $input == array_filter($input, function($t) { return $t instanceof TupleInterface; }) && count($input) === 1)
            return $input[0];

        elseif (is_array($input) && Tuple::IsAnIndexedArray($input) && $input == array_filter($input, function($t) { return $t instanceof Tuple; }))
            return $prefer_and ? new TupleANDWrapper($input) : new TupleORWrapper($input);

        // Case of an array of tupable arrays
        elseif (is_array($input) && Tuple::IsAnIndexedArray($input) && is_array($input[0]))
            return $prefer_and ? new TupleANDWrapper(array_map(function ($t) { return static::LoadTuple($t); }, $input)) : new TupleORWrapper(array_map(function ($t) { return static::LoadTuple($t); }, $input));

        // Case of ['id' => 2] => ['id', '=', 2]
        elseif (is_array($input) && !Tuple::IsAnIndexedArray($input) && count($input) == 1)
            return new ComparisonTuple([array_keys($input)[0], '=', array_values($input)[0]]);

        // Case of ['id', '=', 2]
        elseif (is_array($input) && count($input) == 3 && ComparisonTuple::ValidateOperator($input[1]))
            return new ComparisonTuple([$input[0], $input[1], $input[2]]);

        // Case of ['id', 'BETWEEN', [2, 3]]
        elseif (is_array($input) && count($input) == 3 && SimpleRangeTuple::ValidateOperator($input[1]))
            return new SimpleRangeTuple([$input[0], $input[1], $input[2]]);

        // Case of ['id', 'IN', [2, 5, 10]]
        elseif (is_array($input) && count($input) == 3 && MultipleRangeTuple::ValidateOperator($input[1]))
            return new MultipleRangeTuple([$input[0], $input[1], $input[2]]);

        else
            throw new TupleException("Unreadable expression");

    }

    /**
     * Checks if an array (or something else) is tupable.
     * @param $input
     * @return bool
     */
    public static function IsTuplable($input) {
        try {
            static::LoadTuple($input);
            return true;
        }
        catch (TupleException $e) {
            return false;
        }
    }

} 