<?php
require __DIR__ . '/../vendor/autoload.php';


try{
    $bb = new \Pix\Bank\BrasilArrecadacaoService();

    $bb->setSandbox(true)
        ->setClientId('')
        ->setClientSecret('')
        ->setNumeroConvenio(12345)
        ->setIndicadorCodigoBarras('S')
        ->setCodigoGuiaRecebimento('')
        ->setEmailDevedor('teste@rlz.com.br')
        ->setCodigoSolicitacaoBancoCentralBrasil('chavepix')
        ->setDescricaoSolicitacaoPagamento('Arrecadação Pix')
        ->setValorOriginalSolicitacao(1000.01)
        ->setCpfDevedor('12345612300')
        ->setNomeDevedor('FULADO DE TAL')
        ->setQuantidadeSegundoExpiracao(3600);

    $bb->createPix();
    echo 'TransactionId: '.$bb->getTransactionId().PHP_EOL;
    echo 'QRCode.......: '.$bb->getQrCode();

}catch (\Exception $e) {
    echo $e->getMessage();
}
