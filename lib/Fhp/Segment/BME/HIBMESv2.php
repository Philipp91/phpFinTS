<?php

namespace Fhp\Segment\BME;

use Fhp\Segment\BaseSegment;

/**
 * Segment: Terminierte SEPA-Sammellastschrift einreichen Parameter
 *
 * @link https://www.hbci-zka.de/dokumente/spezifikation_deutsch/fintsv3/FinTS_3.0_Messages_Geschaeftsvorfaelle_2015-08-07_final_version.pdf
 * Section: C.10.3.3.2.1 c)
 */
class HIBMESv2 extends HIBMESv1
{
    public function createRequestSegment(): BaseSegment
    {
        return HKBMEv2::createEmpty();
    }
}
