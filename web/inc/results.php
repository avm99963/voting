<?php
class results {
  public static $methods = array(0, 1, 2, 3, 4);
  const MAJORITY = 0;
  const REL_MAJORITY = 1;
  const ABS_MAJORITY = 2;
  const QUAL_MAJORITY = 3;
  const DHONDT = 4;

  public static function generateResult($method, $votingId) {
    global $con;

    if (!in_array($method, $methods)) {
      return false;
    }

    $voting_results = voting::defaultResults($votingId);

    if ($voting_results === false) {
      return false;
    }

    print_r($voting_results);

    return true;
  }
}
