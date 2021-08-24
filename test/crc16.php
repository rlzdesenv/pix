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
    // Standard values by BACEN
    $payload = '00020101021126590014br.gov.bcb.pix0114037882390001660219GUIA DE ARRECADACAO52040000530398654041.005802BR5925PREFEITURA MUNICIPAL DE T6015TANGARA DA SERR62290525REC09680023000000000000016304';
    $polynomial = 0x1021;
    $response   = 0xFFFF;

    // Checksum
    if ( ( $length = \strlen($payload) ) > 0 )
    {
        for ( $offset = 0; $offset < $length; $offset++ )
        {
            $response ^= ( \ord( $payload[$offset] ) << 8 );

            for ( $bitwise = 0; $bitwise < 8; $bitwise++ )
            {
                $response = $response << 1;
                if ( $response & 0x10000 )
                { $response ^= $polynomial; }

                $response &= 0xFFFF;
            }
        }
    }
    echo $response;
}
catch ( InvalidPixKeyException $e )
{ /** Retorna que a chave pix está inválida. */ }
catch ( InvalidPixKeyTypeException $e )
{ /** Retorna que a chave pix está inválida. */ }
catch ( InvalidEmvFieldException $e )
{ /** Retorna que algum campo está inválido. */ }
catch ( EmvIdIsRequiredException $e )
{ /** Retorna que um campo obrigatório não foi preenchido. */ }
