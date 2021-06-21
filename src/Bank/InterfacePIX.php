<?php
/**
 * Created by PhpStorm.
 * User: Elvis
 * Date: 03/05/2017
 * Time: 16:14
 */

namespace Pix\Bank;


interface InterfacePIX
{
    public function getTransactionId();
    public function getQrCode();
}
