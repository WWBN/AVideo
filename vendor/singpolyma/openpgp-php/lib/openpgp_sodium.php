<?php

function sodium_make_verifier($pk) {
  return function($m, $s) use ($pk) {
    if($pk instanceof OpenPGP_Message) {
      foreach($pk as $p) {
        if($p instanceof OpenPGP_PublicKeyPacket) {
          if(substr($p->fingerprint, strlen($s->issuer())*-1) == $s->issuer()) {
            $pk = $p;
            break;
          }
        }
      }
    }

    if ($pk->algorithm != 22) throw new Exception("Only EdDSA supported");
    if (bin2hex($pk->key['oid']) != '2b06010401da470f01') throw new Exception("Only ed25519 supported");
    return sodium_crypto_sign_verify_detached(
      implode($s->data),
      hash($s->hash_algorithm_name(), $m, true),
      substr($pk->key['p'], 1)
    );
  };
}