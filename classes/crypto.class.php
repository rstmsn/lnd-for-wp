<?php

/**
 * Crypto
 *
 * A PHP class for encrypt and decrypt content.
 *
 * @author     bjadel
 * @version    0.1.0
 */
class Crypto {

  private static $PWD = "YOUR_PASSWORD_PLEASE_CHANGE";
  private static $CIPHER = "aes-256-gcm";
  private static $TAG_LENGTH = 16;

  public function __construct() {
  }

  public static function encrypt($content) {
    $key = substr(hash('sha256', self::$PWD, true), 0, 32);
    $iv_len = openssl_cipher_iv_length(self::$CIPHER);
    $iv = openssl_random_pseudo_bytes($iv_len);
    $tag = ""; // will be filled by openssl_encrypt
    $ciphertext = openssl_encrypt($content, self::$CIPHER, $key, OPENSSL_RAW_DATA, $iv, $tag, "", self::$TAG_LENGTH);
    $encrypted = base64_encode($iv.$tag.$ciphertext);
    return $encrypted;
  }

  public static function decrypt($encrypted_content) {
    $encrypted = base64_decode($encrypted_content);
    $key = substr(hash('sha256', self::$PWD, true), 0, 32);
    $iv_len = openssl_cipher_iv_length(self::$CIPHER);
    $iv = substr($encrypted, 0, $iv_len);
    $tag = substr($encrypted, $iv_len, self::$TAG_LENGTH);
    $ciphertext = substr($encrypted, $iv_len + self::$TAG_LENGTH);
    $content = openssl_decrypt($ciphertext, self::$CIPHER, $key, OPENSSL_RAW_DATA, $iv, $tag);
    return $content;
  }
}
