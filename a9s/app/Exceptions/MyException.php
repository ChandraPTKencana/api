<?php

namespace App\Exceptions;

use Exception;

class MyException extends Exception {
    private $_data;

  public function __construct($message=null,
                              $code = 0,
                              Exception $previous = null,
                              $options = array('params'))
  {
      parent::__construct((is_array ($message)?json_encode($message):$message), $code, $previous);
  }

  public function getData($assoc = false) {
    return json_decode($this->getMessage(), true) ?? $this->getMessage();
  }


}
