<?php

namespace Fhp\Segment\BSE;

use Fhp\Segment\BaseDeg;
use Fhp\Segment\DSE\MinimaleVorlaufzeitSEPALastschrift;
use Fhp\Segment\DSE\SEPADirectDebitMinimalLeadTimeProvider;

class ParameterTerminierteSEPALastschriftEinreichenV2 extends BaseDeg implements SEPADirectDebitMinimalLeadTimeProvider
{
    /** @var string|null Max Length: 4096 */
    public $zulaessigePurposecodes;

    /** @var string[]|null @Max(9) Max length: 256 */
    public $unterstuetzteSEPADatenformate;

    /** @var string */
    public $minimaleVorlaufzeitCodiert;

    /** @var string */
    public $maximaleVorlaufzeitCodiert;

    public function getMinimalLeadTime(string $seqType, string $coreType = 'B2B'): ?MinimaleVorlaufzeitSEPALastschrift
    {
        $parsed = MinimaleVorlaufzeitSEPALastschrift::parseCoded($this->minimaleVorlaufzeitCodiert);
        return $parsed[$coreType][$seqType] ?? null;
    }
}
