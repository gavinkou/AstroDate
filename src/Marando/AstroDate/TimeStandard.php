<?php

namespace Marando\AstroDate;

/**
 * Represents an astronomical time standard such as TT or TDB
 */
class TimeStandard {

  //----------------------------------------------------------------------------
  // Constructors
  //----------------------------------------------------------------------------

  public function __construct($value) {
    $this->value = $value;
  }

  // // // Static

  /**
   * Coordinated Universal Time (UTC)
   * @return static
   */
  public static function UTC() {
    return new static('UTC');
  }

  /**
   * International Atomic Time (TAI)
   * @return static
   */
  public static function TAI() {
    return new static('TAI');
  }

  /**
   * Terrestrial Dynamic Time (TT or TDT)
   * @return static
   */
  public static function TT() {
    return new static('TT');
  }

  /**
   * Barycentric Dynamic Time (TDB)
   * @return static
   */
  public static function TDB() {
    return new static('TDB');
  }

  //----------------------------------------------------------------------------
  // Properties
  //----------------------------------------------------------------------------

  /**
   * Value of this instance
   * @var string
   */
  protected $value;

  //----------------------------------------------------------------------------
  // Functions
  //----------------------------------------------------------------------------
  // // // Overrides

  /**
   * Represents this instance as a string
   * @return string
   */
  public function __toString() {
    return (string)$this->value;
  }

}
