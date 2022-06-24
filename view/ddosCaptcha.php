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
    //return ;
}

session_start(); // inicial a sessao
if(!empty($_SESSION['captcha_validated'])){
    session_write_close();
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
        echo "<h1>Correct captcha</h1>";
        echo "<a href='?return=1'>Return</a>";
        $_SESSION['captcha_validated'] = 1;
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
            <img src="?captcha=1">
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

