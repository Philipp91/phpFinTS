<?php

namespace Fhp\Segment\DSE;

use Fhp\Segment\BaseSegment;
use Fhp\Segment\Common\Btg;
use Fhp\Segment\Common\Kti;
use Fhp\Segment\SegmentInterface;
use Fhp\Syntax\Bin;

interface HIDXES extends SegmentInterface
{
    public function getParameter(): SEPADirectDebitMinimalLeadTimeProvider;

    public function createRequestSegment(
        Kti $kontoverbindungInternational,
        string $sepaDescriptor,
        Bin $sepaPainMessage,
        ?bool $einzelbuchungGewuenscht,
        ?Btg $summenfeld,
    ): BaseSegment;
}
