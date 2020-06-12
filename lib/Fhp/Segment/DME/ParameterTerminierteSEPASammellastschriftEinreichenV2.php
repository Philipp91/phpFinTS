<?php

namespace Fhp\Segment\DME;

use Fhp\Segment\DSE\ParameterTerminierteSEPAEinzellastschriftEinreichenV2;

class ParameterTerminierteSEPASammellastschriftEinreichenV2 extends ParameterTerminierteSEPAEinzellastschriftEinreichenV2
{
    /** @var int */
    public $maximaleAnzahlDirectDebitTransferTransactionInformation;

    /** @var bool */
    public $summenfeldBenoetigt;

    /** @var bool */
    public $einzelbuchungErlaubt;
}
