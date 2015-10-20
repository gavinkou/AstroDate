<?php

namespace Marando\AstroDate;

class TimeStd {

  protected $value;

  public function __construct($value) {
    $this->value = $value;
  }

  public static function UTC() {
    return new static('UTC');
  }

  public static function TAI() {
    return new static('TAI');
  }

  public static function TT() {
    return new static('TT');
  }

  public static function TDB() {
    return new static('TDB');
  }

  public function __toString() {
    return (string)$this->value;
  }

}
