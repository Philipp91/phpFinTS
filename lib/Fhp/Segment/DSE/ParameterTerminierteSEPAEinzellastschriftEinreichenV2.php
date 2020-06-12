<?php

namespace Fhp\Segment\DSE;

use Fhp\Segment\BaseDeg;

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

    public function getMinimalLeadTime(string $seqType, string $coreType = 'CORE'): ?MinimaleVorlaufzeitSEPALastschrift
    {
        $parsed = MinimaleVorlaufzeitSEPALastschrift::parseCoded($this->minimaleVorlaufzeitCodiert);
        return $parsed[$coreType][$seqType] ?? null;
    }
}
