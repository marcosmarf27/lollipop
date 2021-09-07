<?php

class TesteEmail extends TPage
{

    function __construct()
    {
        parent::__construct();


$email = new \SendGrid\Mail\Mail(); 
$email->setFrom("extensao.russas@ufc.br", "Central de Estágios");
$email->setSubject("Estágios não aprovados");

$para = [
    "marcosmarf27@gmail.com" => "User 3",
    "jamelo.palhano@gmail.com" => "User 4",
    "lollipop.palhano@gmail.com" => "User 5"
];
//$email->addTo("marcosmarf27@gmail.com", "Marcos");
$email->addTos($para);
//$email->addContent("text/plain", "conteudo em massa");
$email->addContent("text/html", "<h1>Titulo</h1> <br> conteudo massa testando html");

$key      = parse_ini_file('sendgrid.ini')['key'];
$sendgrid = new \SendGrid($key);
try
{
    $resposta = $sendgrid->send($email);

   
    var_dump($resposta->statusCode());
    var_dump($resposta->headers());
    var_dump($resposta->body());

   
}
catch (Exception $e)
{
    var_dump( "Caught exception" . $e->getMessage());
}
    }
}
