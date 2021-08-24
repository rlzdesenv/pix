<?php
require __DIR__ . '/../vendor/autoload.php';

use Piggly\Pix\DynamicPayload;
use Piggly\Pix\Exceptions\EmvIdIsRequiredException;
use Piggly\Pix\Exceptions\InvalidEmvFieldException;
use Piggly\Pix\Exceptions\InvalidPixKeyException;
use Piggly\Pix\Exceptions\InvalidPixKeyTypeException;
use Piggly\Pix\StaticPayload;

try
{
    // Pix estático
    // Obtém os dados pix do usuário
    // -> Dados obrigatórios
    //$keyType  = 'CNPJ';//filter_input( INPUT_POST, 'CNPJ', FILTER_SANITIZE_STRING);
    //$keyValue = '03788239000166';//filter_input( INPUT_POST, '03788239000166', FILTER_SANITIZE_STRING);
    //$merchantName = 'Prefeitura Municipal de Tangará da serra';//filter_input( INPUT_POST, 'merchantCity', FILTER_SANITIZE_STRING);
    //$merchantCity = 'Tangará da Serra';//filter_input( INPUT_POST, 'TANGARA DA SERRA', FILTER_SANITIZE_STRING);

    // -> Dados opcionais
    //$amount = 1;//filter_input( INPUT_POST, 1, FILTER_SANITIZE_STRING);
    //$tid = filter_input( INPUT_POST, 'tid', FILTER_SANITIZE_STRING);
    //$description = 'Guia de Arrecadação';//filter_input( INPUT_POST, 'Guia de Arrecadacao', FILTER_SANITIZE_STRING);

    /*$payload =
        (new StaticPayload())
            ->setAmount($amount)
            ->setTid($tid)
            ->setDescription($description)
            ->setPixKey($keyType, $keyValue)
            ->setMerchantName($merchantName)
            ->setMerchantCity($merchantCity);*/

    // Pix dinâmico
    // Obtém os dados pix do usuário
    // -> Dados obrigatórios
    /*$merchantName = filter_input( INPUT_POST, 'merchantName', FILTER_SANITIZE_STRING);
    $merchantCity = filter_input( INPUT_POST, 'merchantCity', FILTER_SANITIZE_STRING);

    // Obtém os dados do SPI para o Pix
    $payload =
        (new DynamicPayload())
            ->setUrl($spiUrl) // URL do Pix no SPI
            ->setMerchantName($merchantName)
            ->setMerchantCity($merchantCity);
*/
    // Continue o código

    // Código pix
    //echo $payload->getPixCode();
    //$qrcode = $payload->getPixCode();
    // QR Code
    //echo $qrcode;
    //echo '<img style="margin:12px auto" src="'.$payload->getQRCode().'" alt="QR Code de Pagamento" />';

    $pix = new StaticPayload();

    $pix->setPixKey(\Piggly\Pix\Parser::KEY_TYPE_DOCUMENT, '03788239000166')
        ->setMerchantName('MUNICÍPIO DE TANGARÁ DA SERRA')
        ->setMerchantCity('TANGARA DA SERRA')
        ->setTid('REC0968002391000003019021')
        ->setAmount(1584.31);

    $qrcode = $pix->getPixCode();
    echo $qrcode;
}
catch ( InvalidPixKeyException $e )
{ /** Retorna que a chave pix está inválida. */ }
catch ( InvalidPixKeyTypeException $e )
{ /** Retorna que a chave pix está inválida. */ }
catch ( InvalidEmvFieldException $e )
{ /** Retorna que algum campo está inválido. */ }
catch ( EmvIdIsRequiredException $e )
{ /** Retorna que um campo obrigatório não foi preenchido. */ }
