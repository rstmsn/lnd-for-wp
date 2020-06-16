<?php

/**
 * product
 *
 * A PHP class for save content.
 *
 * @author     bjadel
 * @version    0.1.0
 *
 * @package    lnd-for-wp
 */

class ContentStorage {

  private $content_storage = [];
  private $invoice_storage = [];

  private static $instance = null;

  // The constructor is private
  // to prevent initiation with outer code.
  private function __construct()
  {
    // The expensive process (e.g.,db connection) goes here.
  }

  // The object is created from within the class itself
  // only if the class has no instance.
  public static function getInstance()
  {
    if (self::$instance == null)
    {
      self::$instance = new ContentStorage();
    }

    return self::$instance;
  }

  public function attach_content( $hash, $content) {
    $this->content_storage += [$hash => $content];
  }

  public function get_content( $hash ) {
    return $this->content_storage[$hash];
  }

  public function get_content_with_invoice( $invoicehash ) {
    return $this->get_content($this->get_content_hash($invoicehash));
  }

  public function attach_invoice( $hash, $contentHash) {
    $this->invoice_storage += [$hash => $contentHash];
  }

  public function get_content_hash( $hash ) {
    return $this->invoice_storage[$hash];
  }

  public function generate_content_hash($string1, $string2) {
    $raw = "#" . $string1 . "#" . $string2 . "#";
    return md5($raw);
  }

  public function serialize_all() {
    return "content:" . serialize($this->content_storage) . "Invoice:" . serialize($this->invoice_storage) . "Contenthash:" . $this->generate_content_hash("1", "https://dev.adelberg-online.de/?p=394");
  }

  public function get_version() {
    return "0.1.0";
  }
}
