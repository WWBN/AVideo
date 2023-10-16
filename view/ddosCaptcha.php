<?php

function captcha($maxFontSize, $quantidade_letras){
    $altura = $maxFontSize*2;
    $largura = ($maxFontSize*$quantidade_letras)+$altura;
    $imagem = imagecreate($largura,$altura); // define a largura e a altura da imagem
    $fonte = "../objects/monof55.ttf"; //voce deve ter essa ou outra fonte de sua preferencia em sua pasta
    $preto  = imagecolorallocate($imagem,0,0,0); // define a cor preta

    $palavra = substr(str_shuffle("AaBbCcDdEeFfGgHhIiJjKkLlMmNnPpQqRrSsTtUuVvYyXxWwZz23456789"),0,($quantidade_letras));
    $_SESSION["palavra"] = $palavra; // atribui para a sessao a palavra gerada
    for($i = 1; $i <= $quantidade_letras; $i++){
        $branco = imagecolorallocate($imagem,rand(200,255),rand(200,255),rand(200,255)); // define a cor branca
        $tamanho_fonte = rand(18,$maxFontSize);
        imagettftext($imagem,$tamanho_fonte,rand(-25,25),($maxFontSize*$i),
        ($tamanho_fonte + 10),$branco,$fonte,substr($palavra,($i-1),1));
    }
    imagejpeg($imagem); // gera a imagem
    imagedestroy($imagem); // limpa a imagem da memoria
}
function percentloadavg(){
    $cpu_count = 1;
    if(is_file('/proc/cpuinfo')) {
        $cpuinfo = file_get_contents('/proc/cpuinfo');
        preg_match_all('/^processor/m', $cpuinfo, $matches);
        $cpu_count = count($matches[0]);
    }

    $sys_getloadavg = sys_getloadavg();
    $sys_getloadavg[0] = $sys_getloadavg[0] / $cpu_count;
    $sys_getloadavg[1] = $sys_getloadavg[1] / $cpu_count;
    $sys_getloadavg[2] = $sys_getloadavg[2] / $cpu_count;

    return $sys_getloadavg;
}

$percentloadavg = percentloadavg();

if($percentloadavg[0]<0.5){
    return ;
}

$ua = @$_SERVER['HTTP_USER_AGENT'];

if (!empty($_SERVER['HTTP_USER_AGENT']) && preg_match("/AVideo(.*)/", $ua)) {
    return ;
}

$ip = uniqid();
if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
    $ip = $_SERVER['HTTP_CLIENT_IP'];
} elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
    $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
} else {
    $ip = $_SERVER['REMOTE_ADDR'];
}

$byPassCaptcha = [
    '/Live.on_/',
    '/objects.aVideoEncoder/',
    ];

foreach($byPassCaptcha as $regExp){
    if(preg_match($regExp, $_SERVER['PHP_SELF'])){
        error_log("AVideo ignore captcha {$ip} PHP_SELF={$_SERVER['PHP_SELF']}");
        return;
    }
}

$ignoreLog = ['/view/xsendfile.php'];
if(!in_array($_SERVER['PHP_SELF'], $ignoreLog) || preg_match('/(bot|spider|crawl)/i', $_SERVER['HTTP_USER_AGENT'])){
    error_log("AVideo captcha {$ip} PHP_SELF={$_SERVER['PHP_SELF']} HTTP_USER_AGENT={$ua}");
}

session_name(md5($ip));
session_start(); // inicial a sessao
if(!empty($_SESSION['captcha_validated'])){
    _session_write_close();
    return ;
}
if(!empty($_GET['captcha'])){
   header("Content-type: image/jpeg"); // define o tipo do arquivo
    $maxFontSize = 28;
    $quantidade_letras = 6; // recebe a quantidade de letras que o captcha terá
    captcha($maxFontSize, $quantidade_letras);
    // executa a funcao captcha passando os parametros recebidos
    exit;
}else if(!empty($_GET['validate'])){
    if (strtolower($_POST["palavra"]) == strtolower($_SESSION["palavra"])){
        $_SESSION['captcha_validated'] = 1;
        _session_write_close();
        return ;
    }else{
        echo "<h1>Wrong captcha</h1>";
        echo "<a href='?return=1'>Return</a>";
    }
    exit;
}
?>
<!DOCTYPE html>
<html>
    <head>
        <title>Captcha</title>
    <link href="view/bootstrap/css/bootstrap.min.css" rel="stylesheet" type="text/css"/>
    <script src="node_modules/jquery/dist/jquery.min.js"></script>
    <script src="view/bootstrap/js/bootstrap.min.js" type="text/javascript"></script>
    <body class="">
       <div class=""container>
           <img src="videos/userPhoto/logo.png" class="img img-responsive "><br>
            <img src="?captcha=1" class="img img-responsive ">
            <form action="?validate=1" name="form" method="post" >
            <input type="text" name="palavra" class="form-control"  />
            <input type="submit" value="Validate Captcha" class="btn btn-primary" />
            </form>
            <?php
            //var_dump($percentloadavg);
            ?>
       </div>
        <script>
            $(document).ready(function () {



            });
        </script>
    </body>
</html>
<?php
exit;
?>

