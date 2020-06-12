<?php

namespace Fhp\Segment\BME;

use Fhp\Segment\BSE\ParameterTerminierteSEPAEinzellastschriftEinreichenV1;

class ParameterTerminierteSEPASammellastschriftEinreichenV1 extends ParameterTerminierteSEPAEinzellastschriftEinreichenV1
{
    /** @var int */
    public $maximaleAnzahlDirectDebitTransferTransactionInformation;

    /** @var bool */
    public $summenfeldBenoetigt;

    /** @var bool */
    public $einzelbuchungErlaubt;
}
