<?php

namespace Fhp\Model;

use Fhp\Segment\VPP\HIVPPv1;
use Fhp\Syntax\Bin;

/** Application code should not interact directly with this type, see {@link VopConfirmationRequest instead}. */
class VopConfirmationRequestImpl implements VopConfirmationRequest
{
    private HIVPPv1 $hivpp;
    private ?string $verificationResult;
    private ?string $verificationNotApplicableReason;

    public function __construct(
        HIVPPv1 $hivpp,
        ?string $verificationResult,
        ?string $verificationNotApplicableReason,
    ) {
        $this->hivpp = $hivpp;
        $this->verificationResult = $verificationResult;
        $this->verificationNotApplicableReason = $verificationNotApplicableReason;
    }

    public function getVopId(): Bin
    {
        return $this->hivpp->vopId;
    }

    public function getExpiration(): ?\DateTime
    {
        return $this->hivpp->vopIdGueltigBis?->asDateTime();
    }

    public function getInformationForUser(): ?string
    {
        return $this->hivpp->aufklaerungstextAutorisierungTrotzAbweichung;
    }

    public function getVerificationResult(): ?string
    {
        return $this->verificationResult;
    }

    public function getVerificationNotApplicableReason(): ?string
    {
        return $this->verificationNotApplicableReason;
    }

    public function getReportDescriptor(): ?string
    {
        return $this->hivpp->paymentStatusReportDescriptor;
    }

    public function getReport(): ?Bin
    {
        return $this->hivpp->paymentStatusReport;
    }

    // TODO But what about the DEG format?
}
