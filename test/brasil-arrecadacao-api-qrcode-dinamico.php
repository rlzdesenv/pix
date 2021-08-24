<?php
require __DIR__ . '/../vendor/autoload.php';


try{
    $bb = new \Pix\Bank\BrasilArrecadacaoService();

    $bb->setSandbox(true)
        ->setAppKey('d27bf7790dffabf0136fe17df0050256b981a5be')
        ->setClientId('eyJpZCI6IjY4MGJiZmItMzRiZS00Yzc2LWIiLCJjb2RpZ29QdWJsaWNhZG9yIjowLCJjb2RpZ29Tb2Z0d2FyZSI6MjEyNDgsInNlcXVlbmNpYWxJbnN0YWxhY2FvIjoxfQ')
        ->setClientSecret('eyJpZCI6Ijk2YjY0MGMtNDMzZC00ODVkLThkNzUtNDJlYjEiLCJjb2RpZ29QdWJsaWNhZG9yIjowLCJjb2RpZ29Tb2Z0d2FyZSI6MjEyNDgsInNlcXVlbmNpYWxJbnN0YWxhY2FvIjoxLCJzZXF1ZW5jaWFsQ3JlZGVuY2lhbCI6MSwiYW1iaWVudGUiOiJob21vbG9nYWNhbyIsImlhdCI6MTYyOTgxMjIxOTIyNH0')
        ->setNumeroConvenio(96800)
        ->setIndicadorCodigoBarras('S')
        ->setCodigoGuiaRecebimento('81630000001456744512021062492000030181202143')
        //->setEmailDevedor('fernando@rlz.com.br')
        ->setCodigoSolicitacaoBancoCentralBrasil('e2572aa4-52d6-4527-bc69-60c0699ea50d')
        ->setDescricaoSolicitacaoPagamento('Arrecadação Pix')
        ->setValorOriginalSolicitacao(145.67)
        ->setCpfDevedor('05371336826')
        //->setCnpjDevedor('00394460005887')
        ->setNomeDevedor('ADALTO ALVES DA SILVA')
        ->setVencimento(new \DateTime('2021-08-23'));


    $bb->createPix();
    echo 'TransactionId: '.$bb->getTransactionId().PHP_EOL;
    echo 'QRCode.......: '.$bb->getQrCode();


    /*$cpf = '00523612000133';
    echo (float)$cpf;*/

}catch (\Exception $e) {
    echo $e->getMessage();
}
