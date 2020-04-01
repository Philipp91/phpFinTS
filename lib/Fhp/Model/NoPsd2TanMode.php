<?php

namespace Fhp\Model;

/**
 * This is a placeholder used instead of a real {@link TanMode} in order to signal that the bank's HBCI interface
 * supports no strong authentication whatsoever and thus also no TAN modes. While it should still support the
 * PIN/TAN authentication scheme (that's the only one that this library implements), not supporting the TAN part of
 * means, in times of PSD2 regulations, that the HBCI interface is limited to read-only operations (like reading
 * accounts and statements) and a separate login (through an app or web UI) is required regularly for the HBCI
 * access to keep working.
 */
final class NoPsd2TanMode implements TanMode
{
    /**
     * Note: This ID is (also) used to indicate a single-step TAN mode.
     */
    const ID = 999;

    /** {@inheritdoc} */
    public function getId(): int
    {
        return self::ID;
    }

    /** {@inheritdoc} */
    public function getName(): string
    {
        return 'No PSD2/TANs supported';
    }

    /** {@inheritdoc} */
    public function getChallengeLabel(): string
    {
        return '';
    }

    /** {@inheritdoc} */
    public function getMaxChallengeLength(): int
    {
        return 0;
    }

    /** {@inheritdoc} */
    public function getMaxTanLength(): int
    {
        return 0;
    }

    /** {@inheritdoc} */
    public function getTanFormat(): int
    {
        return 0;
    }

    /** {@inheritdoc} */
    public function needsTanMedium(): bool
    {
        return false;
    }

    /** {@inheritdoc} */
    public function getSmsAbbuchungskontoErforderlich(): bool
    {
        return false;
    }

    /** {@inheritdoc} */
    public function getAuftraggeberkontoErforderlich(): bool
    {
        return false;
    }

    /** {@inheritdoc} */
    public function getChallengeKlasseErforderlich(): bool
    {
        return false;
    }

    /** {@inheritdoc} */
    public function getAntwortHhdUcErforderlich(): bool
    {
        return false;
    }
}
