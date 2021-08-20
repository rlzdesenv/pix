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
    private $codigoSolicitacaoBancoCentralBrasil;
    private $descricaoSolicitacaoPagamento;
    private $valorOriginalSolicitacao;
    private $cpfDevedor;
    private $cnpjDevedor;
    private $nomeDevedor;
    private $vencimento;
    private $listaInformacaoAdicional;
    private $clientId;
    private $clientSecret;
    private $sandbox;
    private $transactionId;
    private $qrCode;

    /**
     * @var ApcuCachePool
     */
    private $cache;

    private $token;
    private $client;
    private $base_uri;
    private $base_uri_token;
    private $base_type_gw;
    private $base_gw_key;

    /**
     * BrasilArrecadacao constructor.
     * @param int $numeroConvenio
     * @param string $indicadorCodigoBarras
     * @param string $codigoGuiaRecebimento
     * @param string $emailDevedor
     * @param int $codigoPaisTelefoneDevedor
     * @param int $dddTelefoneDevedor
     * @param string $numeroTelefoneDevedor
     * @param string $codigoSolicitacaoBancoCentralBrasil
     * @param string $descricaoSolicitacaoPagamento
     * @param numeric $valorOriginalSolicitacao
     * @param string $cpfDevedor
     * @param string $cnpjDevedor
     * @param string $nomeDevedor
     * @param DateTime $vencimento
     * @param $listaInformacaoAdicional
     * @param string $clientId
     * @param string $clientSecret
     * @param bool $sandbox
     */
    public function __construct(int $numeroConvenio = null, string $indicadorCodigoBarras = null, string $codigoGuiaRecebimento = null,
                                string $emailDevedor = null, int $codigoPaisTelefoneDevedor = null, int $dddTelefoneDevedor = null,
                                string $numeroTelefoneDevedor = null, string $codigoSolicitacaoBancoCentralBrasil = null,
                                string $descricaoSolicitacaoPagamento = null, $valorOriginalSolicitacao = null, string $cpfDevedor = null,
                                string $cnpjDevedor = null, string $nomeDevedor = null, int $vencimento = null,
                                $listaInformacaoAdicional = null, string $clientId = null, string $clientSecret = null, bool $sandbox = false)
    {
        $this->cache = new ApcuCachePool();
        $this->numeroConvenio = $numeroConvenio;
        $this->indicadorCodigoBarras = $indicadorCodigoBarras;
        $this->codigoGuiaRecebimento = $codigoGuiaRecebimento;
        $this->emailDevedor = $emailDevedor;
        $this->codigoPaisTelefoneDevedor = $codigoPaisTelefoneDevedor;
        $this->dddTelefoneDevedor = $dddTelefoneDevedor;
        $this->numeroTelefoneDevedor = $numeroTelefoneDevedor;
        $this->codigoSolicitacaoBancoCentralBrasil = $codigoSolicitacaoBancoCentralBrasil;
        $this->descricaoSolicitacaoPagamento = $descricaoSolicitacaoPagamento;
        $this->valorOriginalSolicitacao = $valorOriginalSolicitacao;
        $this->cpfDevedor = $cpfDevedor;
        $this->cnpjDevedor = $cnpjDevedor;
        $this->nomeDevedor = $nomeDevedor;
        $this->vencimento = $vencimento;
        $this->listaInformacaoAdicional = $listaInformacaoAdicional;
        $this->clientId = $clientId;
        $this->clientSecret = $clientSecret;
        $this->sandbox = $sandbox;

        $this->client = new Client([
            'headers' => [
                'Content-Type' => 'application/json; charset=utf-8',
                'Accept' => 'application/json; charset=utf-8'
            ],
            'verify' => false
        ]);
    }

    private function setVariables() {
        if ($this->isSandbox()) {
            $this->base_uri = 'https://api.sandbox.bb.com.br/pix-bb/v1';
            $this->base_uri_token = 'https://oauth.sandbox.bb.com.br/oauth/token';
            $this->base_type_gw = 'gw-dev-app-key';
            $this->base_gw_key = 'd27bd77908ffab901368e17de0050656b9d1a5bf';
            $this->setClientId('eyJpZCI6Ijk1OGQwZjQtOSIsImNvZGlnb1B1YmxpY2Fkb3IiOjAsImNvZGlnb1NvZnR3YXJlIjoxNTkzNSwic2VxdWVuY2lhbEluc3RhbGFjYW8iOjF9');
            $this->setClientSecret('eyJpZCI6IiIsImNvZGlnb1B1YmxpY2Fkb3IiOjAsImNvZGlnb1NvZnR3YXJlIjoxNTkzNSwic2VxdWVuY2lhbEluc3RhbGFjYW8iOjEsInNlcXVlbmNpYWxDcmVkZW5jaWFsIjoxLCJhbWJpZW50ZSI6ImhvbW9sb2dhY2FvIiwiaWF0IjoxNjIxMDIwNTUxMDE1fQ');
            $this->setCodigoSolicitacaoBancoCentralBrasil('e2572aa4-52d6-4527-bc69-60c0699ea50d');
            if ($this->getCpfDevedor()) {
                $this->setCpfDevedor('72335607065');
                $this->setNomeDevedor('HELIO FERREIRA PEIXOTO');
            } else {
                $this->setCnpjDevedor('97167096000119');
                $this->setNomeDevedor('DOCERIA DO LAGO CACIQUE');
            };
        } else {
            $this->base_uri = '???'; //Produção
            $this->base_uri_token = '???';
            $this->base_type_gw = 'gw-app-key';
            $this->base_gw_key = '???';
        }
    }

    private function getToken()
    {
        try {
            $key = sha1('pix-bb-arrecadacao' . $this->getNumeroConvenio());
            $item = $this->cache->getItem($key);
            if (!$item->isHit()) {
                $this->setVariables();
                $postToken = new Client(['auth' => [$this->getClientId(), $this->getClientSecret()]]);
                $response = $postToken->request('POST', $this->base_uri_token, [
                    'headers' => [
                        'Content-Type' => 'application/x-www-form-urlencoded',
                        'Cache-Control' => 'no-cache'
                    ],
                    'body' => 'grant_type=client_credentials&scope=pix.arrecadacao-requisicao pix.arrecadacao-info',
                    'verify' => false
                ]);

                if ($response->getStatusCode() === 201) {
                    $result = json_decode($response->getBody()->getContents());

                    $item->set($result->access_token);
                    $item->expiresAfter($result->expires_in);
                    $this->cache->saveDeferred($item);
                    return $item->get();
                }
            }
            return $item->get();
        } catch (RequestException $e) {
            echo $e->getMessage() . PHP_EOL;
        } catch (\Exception $e) {
            echo $e->getMessage() . PHP_EOL;;
        }
    }

    public function createPix()
    {
        try {
            $token = $this->getToken();
            $body = new \stdClass();

            if ($this->getNumeroConvenio()) {
                $body->numeroConvenio = $this->getNumeroConvenio();
            } else {
                throw new \Exception('Número do Convênio não informado.');
            }

            if ($this->getIndicadorCodigoBarras()) {
                $body->indicadorCodigoBarras = $this->getIndicadorCodigoBarras();
            } else {
                throw new \Exception('Indicador de Código de Barras não informado.');
            }

            if ($this->getCodigoGuiaRecebimento()) {
                $body->codigoGuiaRecebimento = $this->getCodigoGuiaRecebimento();
            } else {
                throw new \Exception('Código de Guia de Recebimento não informado.');
            }

            if ($this->getEmailDevedor()) {
                $body->emailDevedor = $this->getEmailDevedor();
            }

            if ($this->getCodigoSolicitacaoBancoCentralBrasil()) {
                $body->codigoSolicitacaoBancoCentralBrasil = $this->getCodigoSolicitacaoBancoCentralBrasil();
            } else {
                throw new \Exception('Chave PIX do Recebedor não informado.');
            }

            $body->descricaoSolicitacaoPagamento = $this->getDescricaoSolicitacaoPagamento();

            if ($this->getValorOriginalSolicitacao() && $this->getValorOriginalSolicitacao() > 0) {
                $body->valorOriginalSolicitacao = $this->getValorOriginalSolicitacao();
            } else {
                throw new \Exception('Valor não pode ser zerado!!!');
            }

            if ($this->getCpfDevedor()) {
                $body->cpfDevedor = (float)$this->getCpfDevedor();
            } elseif ($this->getCnpjDevedor()){
                $body->cnpjDevedor = (float)$this->getCnpjDevedor();
            } else {
                throw new \Exception('CPF ou CNPJ não informado.');
            }

            $body->nomeDevedor = $this->getNomeDevedor();

            $this->vencimento->setTime(23, 55, 00);
            $expiracao = $this->vencimento->getTimestamp() - (new DateTime())->getTimestamp();

            $body->quantidadeSegundoExpiracao = $expiracao ?: 600;
            $this->setVariables();

            //Requisição HTTPS
            $res = $this->client->request('POST', $this->base_uri.'/arrecadacao-qrcodes', [
                'headers' => ['Authorization' => 'Bearer '.$token],
                'query' => [$this->base_type_gw => $this->base_gw_key],
                'json' => $body
            ]);

            if ($res->getStatusCode() === 200) {
                $retorno = json_decode($res->getBody()->getContents());
                $this->setTransactionId($retorno->codigoConciliacaoSolicitante);
                $this->setQrCode($retorno->qrCode);
            }
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


    /**
     * @return bool
     */
    public function isSandbox(): bool
    {
        return $this->sandbox;
    }

    /**
     * @param bool $sandbox
     * @return BrasilArrecadacaoService
     */
    public function setSandbox(bool $sandbox): BrasilArrecadacaoService
    {
        $this->sandbox = $sandbox;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getNumeroConvenio()
    {
        return $this->numeroConvenio;
    }

    /**
     * @param mixed $numeroConvenio
     * @return BrasilArrecadacaoService
     */
    public function setNumeroConvenio($numeroConvenio): BrasilArrecadacaoService
    {
        $this->numeroConvenio = $numeroConvenio;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getIndicadorCodigoBarras()
    {
        return $this->indicadorCodigoBarras;
    }

    /**
     * @param mixed $indicadorCodigoBarras
     * @return BrasilArrecadacaoService
     */
    public function setIndicadorCodigoBarras($indicadorCodigoBarras): BrasilArrecadacaoService
    {
        $this->indicadorCodigoBarras = $indicadorCodigoBarras;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getCodigoGuiaRecebimento()
    {
        return $this->codigoGuiaRecebimento;
    }

    /**
     * @param mixed $codigoGuiaRecebimento
     * @return BrasilArrecadacaoService
     */
    public function setCodigoGuiaRecebimento($codigoGuiaRecebimento): BrasilArrecadacaoService
    {
        $this->codigoGuiaRecebimento = $codigoGuiaRecebimento;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getEmailDevedor()
    {
        return $this->emailDevedor;
    }

    /**
     * @param mixed $emailDevedor
     * @return BrasilArrecadacaoService
     */
    public function setEmailDevedor($emailDevedor): BrasilArrecadacaoService
    {
        $this->emailDevedor = $emailDevedor;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getCodigoPaisTelefoneDevedor()
    {
        return $this->codigoPaisTelefoneDevedor;
    }

    /**
     * @param mixed $codigoPaisTelefoneDevedor
     * @return BrasilArrecadacaoService
     */
    public function setCodigoPaisTelefoneDevedor($codigoPaisTelefoneDevedor): BrasilArrecadacaoService
    {
        $this->codigoPaisTelefoneDevedor = $codigoPaisTelefoneDevedor;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getDddTelefoneDevedor()
    {
        return $this->dddTelefoneDevedor;
    }

    /**
     * @param mixed $dddTelefoneDevedor
     * @return BrasilArrecadacaoService
     */
    public function setDddTelefoneDevedor($dddTelefoneDevedor): BrasilArrecadacaoService
    {
        $this->dddTelefoneDevedor = $dddTelefoneDevedor;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getNumeroTelefoneDevedor()
    {
        return $this->numeroTelefoneDevedor;
    }

    /**
     * @param mixed $numeroTelefoneDevedor
     * @return BrasilArrecadacaoService
     */
    public function setNumeroTelefoneDevedor($numeroTelefoneDevedor): BrasilArrecadacaoService
    {
        $this->numeroTelefoneDevedor = $numeroTelefoneDevedor;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getCodigoSolicitacaoBancoCentralBrasil()
    {
        return $this->codigoSolicitacaoBancoCentralBrasil;
    }

    /**
     * @param mixed $codigoSolicitacaoBancoCentralBrasil
     * @return BrasilArrecadacaoService
     */
    public function setCodigoSolicitacaoBancoCentralBrasil($codigoSolicitacaoBancoCentralBrasil): BrasilArrecadacaoService
    {
        $this->codigoSolicitacaoBancoCentralBrasil = $codigoSolicitacaoBancoCentralBrasil;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getDescricaoSolicitacaoPagamento()
    {
        return $this->descricaoSolicitacaoPagamento;
    }

    /**
     * @param mixed $descricaoSolicitacaoPagamento
     * @return BrasilArrecadacaoService
     */
    public function setDescricaoSolicitacaoPagamento($descricaoSolicitacaoPagamento): BrasilArrecadacaoService
    {
        $this->descricaoSolicitacaoPagamento = $descricaoSolicitacaoPagamento;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getValorOriginalSolicitacao()
    {
        return $this->valorOriginalSolicitacao;
    }

    /**
     * @param mixed $valorOriginalSolicitacao
     * @return BrasilArrecadacaoService
     */
    public function setValorOriginalSolicitacao($valorOriginalSolicitacao): BrasilArrecadacaoService
    {
        $this->valorOriginalSolicitacao = $valorOriginalSolicitacao;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getCpfDevedor()
    {
        return $this->cpfDevedor;
    }

    /**
     * @param mixed $cpfDevedor
     * @return BrasilArrecadacaoService
     */
    public function setCpfDevedor($cpfDevedor): BrasilArrecadacaoService
    {
        $this->cpfDevedor = $cpfDevedor;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getCnpjDevedor()
    {
        return $this->cnpjDevedor;
    }

    /**
     * @param mixed $cnpjDevedor
     * @return BrasilArrecadacaoService
     */
    public function setCnpjDevedor($cnpjDevedor): BrasilArrecadacaoService
    {
        $this->cnpjDevedor = $cnpjDevedor;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getNomeDevedor()
    {
        return $this->nomeDevedor;
    }

    /**
     * @param mixed $nomeDevedor
     * @return BrasilArrecadacaoService
     */
    public function setNomeDevedor($nomeDevedor): BrasilArrecadacaoService
    {
        $this->nomeDevedor = $nomeDevedor;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getListaInformacaoAdicional()
    {
        return $this->listaInformacaoAdicional;
    }

    /**
     * @param mixed $listaInformacaoAdicional
     * @return BrasilArrecadacaoService
     */
    public function setListaInformacaoAdicional($listaInformacaoAdicional): BrasilArrecadacaoService
    {
        $this->listaInformacaoAdicional = $listaInformacaoAdicional;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getClientId()
    {
        return $this->clientId;
    }

    /**
     * @param mixed $clientId
     * @return BrasilArrecadacaoService
     */
    public function setClientId($clientId): BrasilArrecadacaoService
    {
        $this->clientId = $clientId;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getClientSecret()
    {
        return $this->clientSecret;
    }

    /**
     * @param mixed $clientSecret
     * @return BrasilArrecadacaoService
     */
    public function setClientSecret($clientSecret): BrasilArrecadacaoService
    {
        $this->clientSecret = $clientSecret;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getTransactionId()
    {
        return $this->transactionId;
    }

    /**
     * @param mixed $transactionId
     * @return BrasilArrecadacaoService
     */
    public function setTransactionId($transactionId)
    {
        $this->transactionId = $transactionId;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getQrCode()
    {
        return $this->qrCode;
    }

    /**
     * @param mixed $qrCode
     * @return BrasilArrecadacaoService
     */
    public function setQrCode($qrCode): BrasilArrecadacaoService
    {
        $this->qrCode = $qrCode;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getVencimento()
    {
        return $this->vencimento;
    }

    /**
     * @param mixed $vencimento
     * @return BrasilArrecadacaoService
     */
    public function setVencimento(DateTime $vencimento)
    {
        $this->vencimento = $vencimento;
        return $this;
    }


}
