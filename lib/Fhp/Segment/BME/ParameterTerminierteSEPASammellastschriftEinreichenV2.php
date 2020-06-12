<?php

namespace Fhp\Segment\BME;

use Fhp\Segment\BSE\ParameterTerminierteSEPAEinzellastschriftEinreichenV2;

class ParameterTerminierteSEPASammellastschriftEinreichenV2 extends ParameterTerminierteSEPAEinzellastschriftEinreichenV2
{
    /** @var int */
    public $maximaleAnzahlDirectDebitTransferTransactionInformation;

    /** @var bool */
    public $summenfeldBenoetigt;

    /** @var bool */
    public $einzelbuchungErlaubt;
}
