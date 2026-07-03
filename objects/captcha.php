<?php
global $global, $config;
if (!isset($global['systemRootPath'])) {
    require_once '../videos/configuration.php';
}
class Captcha
{
    private $largura;
    private $altura;
    private $tamanho_fonte;
    private $quantidade_letras;

    public function __construct($largura, $altura, $tamanho_fonte, $quantidade_letras)
    {
        $this->largura = $largura;
        $this->altura = $altura;
        $this->tamanho_fonte = $tamanho_fonte;
        $this->quantidade_letras = $quantidade_letras;
    }


    public function getCaptchaImage()
    {
        global $global;
        header('Content-Type: image/jpeg');
        header('Cache-Control: no-store, no-cache, must-revalidate');
        header('Pragma: no-cache');
        header('Expires: 0');
        $imagem = imagecreate($this->largura, $this->altura); // define a largura e a altura da imagem
        $fonte = $global['systemRootPath'] . 'objects/monof55.ttf'; //voce deve ter essa ou outra fonte de sua preferencia em sua pasta
        $preto  = imagecolorallocate($imagem, 0, 0, 0); // define a cor preta
        $branco = imagecolorallocate($imagem, 255, 255, 255); // define a cor branca

        // define a palavra conforme a quantidade de letras definidas no parametro $quantidade_letras
        $letters = 'AaBbCcDdEeFfGgHhIiJjKkLlMmNnPpQqRrSsTtUuVvYyXxWwZz23456789';
        $len = strlen($letters);
        $palavra = '';
        for ($j = 0; $j < $this->quantidade_letras; $j++) {
            $palavra .= $letters[random_int(0, $len - 1)];
        }
        if (User::isAdmin() && empty($_REQUEST['forceCaptcha'])) {
            $palavra = "admin";
        }
        _session_start();
        $_SESSION["palavra"] = $palavra; // atribui para a sessao a palavra gerada
        _error_log("getCaptchaImage: ".$palavra." - session_name ". session_name()." session_id: ". session_id()." IP: ".getRealIpAddr()." UA: ".($_SERVER['HTTP_USER_AGENT'] ?? 'n/a'));
        for ($i = 1; $i <= $this->quantidade_letras; $i++) {
            imagettftext(
                $imagem,
                $this->tamanho_fonte,
                rand(-10, 10),
                ($this->tamanho_fonte*$i),
                ($this->tamanho_fonte + 10),
                $branco,
                $fonte,
                substr($palavra, ($i - 1), 1)
            ); // atribui as letras a imagem
        }
        imagejpeg($imagem); // gera a imagem
        imagedestroy($imagem); // limpa a imagem da memoria
        //_error_log("getCaptchaImage _SESSION[palavra] = ($_SESSION[palavra]) - session_name ". session_name()." session_id: ". session_id());
    }

    public static function validation($word)
    {
        _session_start();
        if (empty($_SESSION["palavra"])) {
            _error_log("Captcha validation Error: you type ({$word}) and session is empty - session_name ". session_name()." session_id: ". session_id());
            return false;
        }
        $stored = $_SESSION["palavra"];
        unset($_SESSION["palavra"]); // always consume on any attempt to prevent brute-force
        if (User::isAdmin() && $stored === 'admin') {
            return true;
        }
        $validation = (strcasecmp($word, $stored) === 0);
        if (!$validation) {
            _error_log("Captcha validation Error: you type ({$word}) and session is ({$stored})- session_name ". session_name()." session_id: ". session_id());
        }
        return $validation;
    }
}
