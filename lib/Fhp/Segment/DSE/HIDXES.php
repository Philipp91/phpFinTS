<?php

namespace Fhp\Segment\DSE;

use Fhp\Segment\SegmentInterface;

interface HIDXES extends SegmentInterface
{
    public function getParameter(): SEPADirectDebitMinimalLeadTimeProvider;
}
