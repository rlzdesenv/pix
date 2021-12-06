<?php

namespace Pix\Bank;

use DateTime;
use Pix\Exception\InvalidArgumentException;
use Cache\Adapter\Apcu\ApcuCachePool;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;

class BrasilArrecadacaoService implements InterfacePIX
{
    private $numeroConvenio;
    private $indicadorCodigoBarras;
    private $codigoGuiaRecebimento;
    private $emailDevedor;
    private $codigoPaisTelefoneDevedor;
    private $dddTelefoneDevedor;
    private $numeroTelefoneDevedor;
    private $chavePIX;
    private $descricaoSolicitacaoPagamento;
    private $valorOriginalSolicitacao;
    private $cpfDevedor;
    private $cnpjDevedor;
    private $nomeDevedor;
    private $vencimento;
    private $listaInformacaoAdicional;
    private $baseURIToken;
    private $baseURI;
    private $appKey;
    private $clientId;
    private $clientSecret;
    private $transactionId;
    private $qrCode;

    /**
     * @var ApcuCachePool
     */
    private $cache;

    private $token;
    private $client;

    /**
     * BrasilArrecadacao constructor.
     * @param int $numeroConvenio
     * @param string $indicadorCodigoBarras
     * @param string $codigoGuiaRecebimento
     * @param string $emailDevedor
     * @param int $codigoPaisTelefoneDevedor
     * @param int $dddTelefoneDevedor
     * @param string $numeroTelefoneDevedor
     * @param string $chavePIX
     * @param string $descricaoSolicitacaoPagamento
     * @param numeric $valorOriginalSolicitacao
     * @param string $cpfDevedor
     * @param string $cnpjDevedor
     * @param string $nomeDevedor
     * @param DateTime $vencimento
     * @param $listaInformacaoAdicional
     * @param string $baseURIToken
     * @param string $baseURI
     * @param string $appKey
     * @param string $clientId
     * @param string $clientSecret
     */
    public function __construct(int $numeroConvenio = null, string $indicadorCodigoBarras = null, string $codigoGuiaRecebimento = null,
                                string $emailDevedor = null, int $codigoPaisTelefoneDevedor = null, int $dddTelefoneDevedor = null,
                                string $numeroTelefoneDevedor = null, string $chavePIX = null,
                                string $descricaoSolicitacaoPagamento = null, $valorOriginalSolicitacao = null, string $cpfDevedor = null,
                                string $cnpjDevedor = null, string $nomeDevedor = null, int $vencimento = null,  $listaInformacaoAdicional = null,
                                string $baseURIToken = null, string $baseURI = null, string $appKey = null,  string $clientId = null,
                                string $clientSecret = null)
    {
        $this->cache = new ApcuCachePool();
        $this->numeroConvenio = $numeroConvenio;
        $this->indicadorCodigoBarras = $indicadorCodigoBarras;
        $this->codigoGuiaRecebimento = $codigoGuiaRecebimento;
        $this->emailDevedor = $emailDevedor;
        $this->codigoPaisTelefoneDevedor = $codigoPaisTelefoneDevedor;
        $this->dddTelefoneDevedor = $dddTelefoneDevedor;
        $this->numeroTelefoneDevedor = $numeroTelefoneDevedor;
        $this->chavePIX = $chavePIX;
        $this->descricaoSolicitacaoPagamento = $descricaoSolicitacaoPagamento;
        $this->valorOriginalSolicitacao = $valorOriginalSolicitacao;
        $this->cpfDevedor = $cpfDevedor;
        $this->cnpjDevedor = $cnpjDevedor;
        $this->nomeDevedor = $nomeDevedor;
        $this->vencimento = $vencimento;
        $this->listaInformacaoAdicional = $listaInformacaoAdicional;
        $this->baseURIToken = $baseURIToken;
        $this->baseURI = $baseURI;
        $this->appKey = $appKey;
        $this->clientId = $clientId;
        $this->clientSecret = $clientSecret;

        $this->client = new Client([
            'headers' => [
                'Content-Type' => 'application/json; charset=utf-8',
                'Accept' => 'application/json; charset=utf-8'
            ],
            'verify' => false
        ]);
    }

    private function getToken()
    {
        try {
            $key = sha1('pix-bb-arrecadacao' . $this->numeroConvenio);
            $item = $this->cache->getItem($key);
            if (!$item->isHit()) {
                $postToken = new Client(['auth' => [$this->clientId, $this->clientSecret]]);
                $response = $postToken->request('POST', $this->baseURIToken, [
                    'headers' => [
                        'Content-Type' => 'application/x-www-form-urlencoded',
                        'Cache-Control' => 'no-cache'
                    ],
                    'body' => 'grant_type=client_credentials&scope=pix.arrecadacao-requisicao pix.arrecadacao-info',
                    'verify' => false
                ]);

                $result = json_decode($response->getBody()->getContents());

                $item->set($result->access_token);
                $item->expiresAfter($result->expires_in);
                $this->cache->saveDeferred($item);
                return $item->get();

            }
            return $item->get();
        } catch (RequestException $e) {
            echo $e->getMessage() . PHP_EOL;
        } catch (\Exception $e) {
            echo $e->getMessage() . PHP_EOL;
        }
    }

    public function createPix()
    {
        try {
            $token = $this->getToken();
            $body = new \stdClass();

            if ($this->numeroConvenio) {
                $body->numeroConvenio = $this->numeroConvenio;
            } else {
                throw new \Exception('Número do Convênio não informado.');
            }

            if ($this->indicadorCodigoBarras) {
                $body->indicadorCodigoBarras = $this->indicadorCodigoBarras;
            } else {
                throw new \Exception('Indicador de Código de Barras não informado.');
            }

            if ($this->codigoGuiaRecebimento) {
                $body->codigoGuiaRecebimento = $this->codigoGuiaRecebimento;
            } else {
                throw new \Exception('Código de Guia de Recebimento não informado.');
            }

            if ($this->chavePIX) {
                $body->codigoSolicitacaoBancoCentralBrasil = $this->chavePIX;
            } else {
                throw new \Exception('Chave PIX do Recebedor não informado.');
            }

            if ($this->emailDevedor) {
                $body->emailDevedor = $this->emailDevedor;
            }

            $body->descricaoSolicitacaoPagamento = $this->descricaoSolicitacaoPagamento;

            if ($this->valorOriginalSolicitacao && $this->valorOriginalSolicitacao > 0) {
                $body->valorOriginalSolicitacao = $this->valorOriginalSolicitacao;
            } else {
                throw new \Exception('Valor não pode ser zerado!!!');
            }

            if ($this->cpfDevedor) {
                $body->cpfDevedor = $this->cpfDevedor;
            } elseif ($this->cnpjDevedor){
                $body->cnpjDevedor = $this->cnpjDevedor;
            } else {
                throw new \Exception('CPF ou CNPJ não informado.');
            }

            $body->nomeDevedor = $this->nomeDevedor;

            $this->vencimento->setTime(23, 55, 00);
            $expiracao = $this->vencimento->getTimestamp() - (new DateTime())->getTimestamp();

            $body->quantidadeSegundoExpiracao = $expiracao ?: 600;
            
            //Requisição HTTPS
            $res = $this->client->request('POST', $this->baseURI.'/arrecadacao-qrcodes', [
                'headers' => ['Authorization' => 'Bearer '.$token],
                'query' => ['gw-dev-app-key' => $this->appKey],
                'json' => $body
            ]);

            $retorno = json_decode($res->getBody()->getContents());
            $this->setTransactionId($retorno->codigoConciliacaoSolicitante);
            $this->setQrCode($retorno->qrCode);

        } catch (RequestException $e) {
            if($e->hasResponse()) {
                $error = json_decode($e->getResponse()->getBody()->getContents());
                $exception = new InvalidArgumentException($error->erros[0]->codigo, $error->erros[0]->mensagem);
                throw new \Exception($error->erros[0]->mensagem, 406, $exception);
            } else {
                throw new \Exception($e->getMessage(), $e->getCode());
            }
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage(), $e->getCode());
        }
    }

    public function setbaseURIToken(string $baseURIToken): BrasilArrecadacaoService
    {
        $this->baseURIToken = $baseURIToken;
        return $this;
    }

    public function setbaseURI(string $baseURI): BrasilArrecadacaoService
    {
        $this->baseURI = $baseURI;
        return $this;
    }

    public function setNumeroConvenio(int $numeroConvenio): BrasilArrecadacaoService
    {
        $this->numeroConvenio = $numeroConvenio;
        return $this;
    }

    public function setIndicadorCodigoBarras(string $indicadorCodigoBarras): BrasilArrecadacaoService
    {
        $this->indicadorCodigoBarras = $indicadorCodigoBarras;
        return $this;
    }

    public function setCodigoGuiaRecebimento(string $codigoGuiaRecebimento): BrasilArrecadacaoService
    {
        $this->codigoGuiaRecebimento = $codigoGuiaRecebimento;
        return $this;
    }

    public function setEmailDevedor(string $emailDevedor): BrasilArrecadacaoService
    {
        $this->emailDevedor = $emailDevedor;
        return $this;
    }

    public function setCodigoPaisTelefoneDevedor(int $codigoPaisTelefoneDevedor): BrasilArrecadacaoService
    {
        $this->codigoPaisTelefoneDevedor = $codigoPaisTelefoneDevedor;
        return $this;
    }

    public function setDddTelefoneDevedor(int $dddTelefoneDevedor): BrasilArrecadacaoService
    {
        $this->dddTelefoneDevedor = $dddTelefoneDevedor;
        return $this;
    }

    public function setNumeroTelefoneDevedor(string $numeroTelefoneDevedor): BrasilArrecadacaoService
    {
        $this->numeroTelefoneDevedor = $numeroTelefoneDevedor;
        return $this;
    }

    public function setChavePIX(string $chavePIX): BrasilArrecadacaoService
    {
        $this->chavePIX = $chavePIX;
        return $this;
    }

    public function setDescricaoSolicitacaoPagamento(string $descricaoSolicitacaoPagamento): BrasilArrecadacaoService
    {
        $this->descricaoSolicitacaoPagamento = $descricaoSolicitacaoPagamento;
        return $this;
    }

    public function setValorOriginalSolicitacao($valorOriginalSolicitacao): BrasilArrecadacaoService
    {
        $this->valorOriginalSolicitacao = $valorOriginalSolicitacao;
        return $this;
    }

    public function setNomeDevedor(string $nomeDevedor): BrasilArrecadacaoService
    {
        $this->nomeDevedor = $nomeDevedor;
        return $this;
    }

    public function setListaInformacaoAdicional($listaInformacaoAdicional): BrasilArrecadacaoService
    {
        $this->listaInformacaoAdicional = $listaInformacaoAdicional;
        return $this;
    }

    public function setAppKey(string $appKey): BrasilArrecadacaoService
    {
        $this->appKey = $appKey;
        return $this;
    }

    public function setClientId(string $clientId): BrasilArrecadacaoService
    {
        $this->clientId = $clientId;
        return $this;
    }

    public function setClientSecret(string $clientSecret): BrasilArrecadacaoService
    {
        $this->clientSecret = $clientSecret;
        return $this;
    }

    public function setTransactionId(string $transactionId)
    {
        $this->transactionId = $transactionId;
        return $this;
    }

    public function setQrCode(string $qrCode): BrasilArrecadacaoService
    {
        $this->qrCode = $qrCode;
        return $this;
    }

    public function setVencimento(DateTime $vencimento)
    {
        $this->vencimento = $vencimento;
        return $this;
    }

    public function setDocumento(string $tipopessoa, $documento)
    {
        if ($tipopessoa === 'Jurídica') {
            $this->cnpjDevedor = (string)(float)$documento;
        } else {
            $this->cpfDevedor = (string)(float)$documento;
        }
        return $this;
    }

    public function getTransactionId()
    {
        return $this->transactionId;
    }

    public function getQrCode()
    {
        return $this->qrCode;
    }



}
