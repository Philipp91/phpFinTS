<?php

namespace Fhp\Action;

use Fhp\BaseAction;
use Fhp\Protocol\BPD;
use Fhp\Protocol\UPD;
use Fhp\Segment\DSE\HIDXES;
use Fhp\Segment\DSE\MinimaleVorlaufzeitSEPALastschrift;

/**
 * Retrieves information about SEPA Direct Debit Requests
 */
class GetSEPADirectDebitParameters extends BaseAction
{
    const SEQUENCE_TYPES = ['FRST', 'OOFF', 'FNAL', 'RCUR'];
    const CORE_TYPES = ['CORE', 'COR1', 'B2B'];

    /** @var string */
    private $coreType;

    /** @var string */
    private $seqType;

    /** @var bool */
    private $singleDirectDebit;

    /** @var MinimaleVorlaufzeitSEPALastschrift|null */
    private $minimalLeadTime;

    public static function create(string $seqType, bool $singleDirectDebit, string $coreType = 'CORE')
    {
        if (!in_array($coreType, self::CORE_TYPES)) {
            throw new \InvalidArgumentException('Unknown CORE type, possible values are ' . implode(', ', self::CORE_TYPES));
        }
        if (!in_array($seqType, self::SEQUENCE_TYPES)) {
            throw new \InvalidArgumentException('Unknown SEPA sequence type, possible values are ' . implode(', ', self::SEQUENCE_TYPES));
        }
        $result = new GetSEPADirectDebitParameters();
        $result->coreType = $coreType;
        $result->seqType = $seqType;
        $result->singleDirectDebit = $singleDirectDebit;
        return $result;
    }

    /** {@inheritdoc} */
    public function createRequest(BPD $bpd, ?UPD $upd)
    {
        switch ($this->coreType) {
            case 'CORE':
            case 'COR1':
                $type = $this->singleDirectDebit ? 'HIDSES' : 'HIDMES';
                break;
            case 'B2B':
                $type = $this->singleDirectDebit ? 'HIBSES' : 'HIBMES';
                break;
            default:
                throw new \InvalidArgumentException('Unknown CORE type, possible values are ' . implode(', ', self::CORE_TYPES));
        }

        /** @var HIDXES $hidxes */
        $hidxes = $bpd->requireLatestSupportedParameters($type);

        $this->minimalLeadTime = $hidxes->getParameter()->getMinimalLeadTime($this->seqType, $this->coreType);

        // No request to the bank required
        return [];
    }

    /**
     * @return MinimaleVorlaufzeitSEPALastschrift|null The information about the lead time for the given Sequence Type and Core Type
     */
    public function getMinimalLeadTime(): ?MinimaleVorlaufzeitSEPALastschrift
    {
        //$this->ensureSuccess();
        return $this->minimalLeadTime;
    }
}
