<?php

if(function_exists('openssl_encrypt')) {
  class OpenSSLWrapper {
    public $cipher, $key, $iv, $key_size, $block_size;


    function __construct($cipher) {
      if($cipher != "CAST5-CFB") throw Exception("OpenSSLWrapper is only used for CAST5 right now");

      $this->cipher = $cipher;
      $this->key_size = 16;
      $this->block_size = 8;
      $this->iv = str_repeat("\0", 8);
    }

    function setKey($key) {
      $this->key = $key;
    }

    function setIV($iv) {
      $this->iv = $iv;
    }

    function encrypt($data) {
      return openssl_encrypt($data, $this->cipher, $this->key, OPENSSL_RAW_DATA|OPENSSL_ZERO_PADDING, $this->iv);
    }

    function decrypt($data) {
      return openssl_decrypt($data, $this->cipher, $this->key, OPENSSL_RAW_DATA|OPENSSL_ZERO_PADDING, $this->iv);
    }
  }
}
