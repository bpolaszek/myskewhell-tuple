<?php
namespace MySkewHell\Tuple;


interface TupleInterface {

    public function __toString();

    public function getValues();

    public function getValuesAsString();

    public function getPlaceHolders();

    public function getPlaceHoldersAsString();

    public function useNamedPlaceHolders($value = null);

    public function wrapFields($value = null);

} 