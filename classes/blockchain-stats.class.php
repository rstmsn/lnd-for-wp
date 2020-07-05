<?php

/**
 * BlockchainStats
 *
 * A PHP class for statistics about the bitcoin blockchain.
 *
 * @author     bjadel
 * @version    0.1.0
 */
class BlockchainStats {

  public function __construct() {
  }

  public static function satToFiat($sats, $fiat="EUR") {

    $curl = curl_init();
    $url = "https://blockchain.info/ticker";
    curl_setopt($curl, CURLOPT_URL, $url);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
    $result = curl_exec($curl);
    $result = json_decode($result, true);
    $bitcoin_rate = $result[$fiat]["15m"];
    $result = $bitcoin_rate * ($sats/100000000);

    curl_close($curl);

    return $result;
}
}
