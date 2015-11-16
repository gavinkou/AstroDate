<?php

/*
 * Copyright (C) 2015 ashley
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 2
 * of the License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA.
 */

namespace Marando\AstroDate;

class Timezone {

  public $offset;
  public $dst;
  public $name;

  public function __construct($offset, $dst, $name) {
    $this->offset = $offset;
    $this->dst    = $dst;
    $this->name   = $name;
  }

  public static function UTC() {
    return new static(0, false, 'UTC');
  }

  public static function EST() {
    return new static(-5, true, 'EST');
  }

  public function __toString() {
    return $this->name;
  }

}
