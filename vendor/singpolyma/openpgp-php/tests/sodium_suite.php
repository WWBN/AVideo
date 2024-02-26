<?php
use PHPUnit\Framework\TestCase;

/* The tests which require phpseclib */

require_once dirname(__FILE__).'/../lib/openpgp.php';
require_once dirname(__FILE__).'/../lib/openpgp_sodium.php';

class SodiumMessageVerification extends TestCase {
  public function oneMessageEdDSA($pkey, $path) {
    $pkeyM = OpenPGP_Message::parse(file_get_contents(dirname(__FILE__) . '/data/' . $pkey));
    $m = OpenPGP_Message::parse(file_get_contents(dirname(__FILE__) . '/data/' . $path));
    $verify = sodium_make_verifier($pkeyM);
    $this->assertSame($m->verified_signatures(array('EdDSA' => $verify)), $m->signatures());
  }

  public function tested25519() {
    $this->oneMessageEdDSA('ed25519.public_key', 'ed25519.sig');
  }
}
