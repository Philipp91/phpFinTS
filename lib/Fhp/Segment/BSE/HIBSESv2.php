<?php

namespace Fhp\Segment\BSE;

use Fhp\Segment\BaseSegment;

/**
 * Segment: Terminierte SEPA-Einzellastschrift einreichen Parameter
 *
 * @link https://www.hbci-zka.de/dokumente/spezifikation_deutsch/fintsv3/FinTS_3.0_Messages_Geschaeftsvorfaelle_2015-08-07_final_version.pdf
 * Section: C.10.2.6.2.2 c)
 */
class HIBSESv2 extends HIBSESv1
{
    public function createRequestSegment(): BaseSegment
    {
        return HKBSEv2::createEmpty();
    }
}
