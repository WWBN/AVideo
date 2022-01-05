<?php
use PHPUnit\Framework\TestCase;

/* The tests which require phpseclib */

require_once dirname(__FILE__).'/../lib/openpgp.php';
require_once dirname(__FILE__).'/../lib/openpgp_crypt_rsa.php';
require_once dirname(__FILE__).'/../lib/openpgp_crypt_symmetric.php';

class MessageVerification extends TestCase {
  public function oneMessageRSA($pkey, $path) {
    $pkeyM = OpenPGP_Message::parse(file_get_contents(dirname(__FILE__) . '/data/' . $pkey));
    $m = OpenPGP_Message::parse(file_get_contents(dirname(__FILE__) . '/data/' . $path));
    $verify = new OpenPGP_Crypt_RSA($pkeyM);
    $this->assertSame($verify->verify($m), $m->signatures());
  }

  public function testUncompressedOpsRSA() {
    $this->oneMessageRSA('pubring.gpg', 'uncompressed-ops-rsa.gpg');
  }

  public function testCompressedSig() {
    $this->oneMessageRSA('pubring.gpg', 'compressedsig.gpg');
  }

  public function testCompressedSigZLIB() {
    $this->oneMessageRSA('pubring.gpg', 'compressedsig-zlib.gpg');
  }

  public function testCompressedSigBzip2() {
    $this->oneMessageRSA('pubring.gpg', 'compressedsig-bzip2.gpg');
  }

  public function testSigningMessages() {
    $wkey = OpenPGP_Message::parse(file_get_contents(dirname(__FILE__) . '/data/helloKey.gpg'));
    $data = new OpenPGP_LiteralDataPacket('This is text.', array('format' => 'u', 'filename' => 'stuff.txt'));
    $sign = new OpenPGP_Crypt_RSA($wkey);
    $m = $sign->sign($data)->to_bytes();
    $reparsedM = OpenPGP_Message::parse($m);
    $this->assertSame($sign->verify($reparsedM), $reparsedM->signatures());
  }

/*
  public function testUncompressedOpsDSA() {
    $this->oneMessageDSA('pubring.gpg', 'uncompressed-ops-dsa.gpg');
  }

  public function testUncompressedOpsDSAsha384() {
    $this->oneMessageDSA('pubring.gpg', 'uncompressed-ops-dsa-sha384.gpg');
  }
*/
}


class KeyVerification extends TestCase {
  public function oneKeyRSA($path) {
    $m = OpenPGP_Message::parse(file_get_contents(dirname(__FILE__) . '/data/' . $path));
    $verify = new OpenPGP_Crypt_RSA($m);
    $this->assertSame($verify->verify($m), $m->signatures());
  }

  public function testHelloKey() {
    $this->oneKeyRSA("helloKey.gpg");
  }
}


class Decryption extends TestCase {
  public function oneSymmetric($pass, $cnt, $path) {
    $m = OpenPGP_Message::parse(file_get_contents(dirname(__FILE__) . '/data/' . $path));
    $m2 = OpenPGP_Crypt_Symmetric::decryptSymmetric($pass, $m);
    while($m2[0] instanceof OpenPGP_CompressedDataPacket) $m2 = $m2[0]->data;
    foreach($m2 as $p) {
      if($p instanceof OpenPGP_LiteralDataPacket) {
        $this->assertEquals($p->data, $cnt);
      }
    }
  }

  public function testDecrypt3DES() {
    $this->oneSymmetric("hello", "PGP\n", "symmetric-3des.gpg");
  }

  public function testDecryptCAST5() { // Requires mcrypt or openssl
    $this->oneSymmetric("hello", "PGP\n", "symmetric-cast5.gpg");
  }

  public function testDecryptBlowfish() {
    $this->oneSymmetric("hello", "PGP\n", "symmetric-blowfish.gpg");
  }

  public function testDecryptAES() {
    $this->oneSymmetric("hello", "PGP\n", "symmetric-aes.gpg");
  }

  public function testDecryptTwofish() {
    if(OpenPGP_Crypt_Symmetric::getCipher(10)[0]) {
      $this->oneSymmetric("hello", "PGP\n", "symmetric-twofish.gpg");
    }
  }

  public function testDecryptSessionKey() {
    $this->oneSymmetric("hello", "PGP\n", "symmetric-with-session-key.gpg");
  }

  public function testDecryptNoMDC() {
    $this->oneSymmetric("hello", "PGP\n", "symmetric-no-mdc.gpg");
  }

  public function testDecryptAsymmetric() {
    $m = OpenPGP_Message::parse(file_get_contents(dirname(__FILE__) . '/data/hello.gpg'));
    $key = OpenPGP_Message::parse(file_get_contents(dirname(__FILE__) . '/data/helloKey.gpg'));
    $decryptor = new OpenPGP_Crypt_RSA($key);
    $m2 = $decryptor->decrypt($m);
    while($m2[0] instanceof OpenPGP_CompressedDataPacket) $m2 = $m2[0]->data;
    foreach($m2 as $p) {
      if($p instanceof OpenPGP_LiteralDataPacket) {
        $this->assertEquals($p->data, "hello\n");
      }
    }
  }

  public function testDecryptRoundtrip() {
    $m = new OpenPGP_Message(array(new OpenPGP_LiteralDataPacket("hello\n")));
    $key = OpenPGP_Message::parse(file_get_contents(dirname(__FILE__) . '/data/helloKey.gpg'));
    $em = OpenPGP_Crypt_Symmetric::encrypt($key, $m);

    foreach($key as $packet) {
	   if(!($packet instanceof OpenPGP_SecretKeyPacket)) continue;
      $decryptor = new OpenPGP_Crypt_RSA($packet);
      $m2 = $decryptor->decrypt($em);

      foreach($m2 as $p) {
        if($p instanceof OpenPGP_LiteralDataPacket) {
          $this->assertEquals($p->data, "hello\n");
        }
      }
    }
  }

  public function testDecryptSecretKey() {
    $key = OpenPGP_Message::parse(file_get_contents(dirname(__FILE__) . '/data/encryptedSecretKey.gpg'));
    $skey = OpenPGP_Crypt_Symmetric::decryptSecretKey("hello", $key[0]);
    $this->assertSame(!!$skey, true);
  }

  public function testEncryptSecretKeyRoundtrip() {
    $key = OpenPGP_Message::parse(file_get_contents(dirname(__FILE__) . '/data/helloKey.gpg'));
    $enkey = OpenPGP_Crypt_Symmetric::encryptSecretKey("password", $key[0]);
    $skey = OpenPGP_Crypt_Symmetric::decryptSecretKey("password", $enkey);
    $this->assertEquals($key[0], $skey);
  }

  public function testAlreadyDecryptedSecretKey() {
    $this->expectException(Exception::class);
    $this->expectExceptionMessage("Data is already unencrypted");
    $key = OpenPGP_Message::parse(file_get_contents(dirname(__FILE__) . '/data/helloKey.gpg'));
    OpenPGP_Crypt_Symmetric::decryptSecretKey("hello", $key[0]);
  }
}

class Encryption extends TestCase {
  public function oneSymmetric($algorithm) {
    $data = new OpenPGP_LiteralDataPacket('This is text.', array('format' => 'u', 'filename' => 'stuff.txt'));
    $encrypted = OpenPGP_Crypt_Symmetric::encrypt('secret', new OpenPGP_Message(array($data)), $algorithm);
    $encrypted = OpenPGP_Message::parse($encrypted->to_bytes());
    $decrypted = OpenPGP_Crypt_Symmetric::decryptSymmetric('secret', $encrypted);
    $this->assertEquals($decrypted[0]->data, 'This is text.');
  }

  public function testEncryptSymmetric3DES() {
    $this->oneSymmetric(2);
  }

  public function testEncryptSymmetricCAST5() {
    $this->oneSymmetric(3);
  }

  public function testEncryptSymmetricBlowfish() {
    $this->oneSymmetric(4);
  }

  public function testEncryptSymmetricAES128() {
    $this->oneSymmetric(7);
  }

  public function testEncryptSymmetricAES192() {
    $this->oneSymmetric(8);
  }

  public function testEncryptSymmetricAES256() {
    $this->oneSymmetric(9);
  }

  public function testEncryptSymmetricTwofish() {
    if(OpenPGP_Crypt_Symmetric::getCipher(10)[0]) {
      $this->oneSymmetric(10);
    }
  }

  public function testEncryptAsymmetric() {
    $key = OpenPGP_Message::parse(file_get_contents(dirname(__FILE__) . '/data/helloKey.gpg'));
    $data = new OpenPGP_LiteralDataPacket('This is text.', array('format' => 'u', 'filename' => 'stuff.txt'));
    $encrypted = OpenPGP_Crypt_Symmetric::encrypt($key, new OpenPGP_Message(array($data)));
    $encrypted = OpenPGP_Message::parse($encrypted->to_bytes());
    $decryptor = new OpenPGP_Crypt_RSA($key);
    $decrypted = $decryptor->decrypt($encrypted);
    $this->assertEquals($decrypted[0]->data, 'This is text.');
  }
}
