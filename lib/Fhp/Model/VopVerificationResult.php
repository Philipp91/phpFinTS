<?php

namespace Fhp\Model;

use Fhp\Protocol\UnexpectedResponseException;

/**
 * Possible outcomes of the Verification of Payee check that the bank did on a transfer we want to execute.
 * @see FinTS_3.0_Messages_Geschaeftsvorfaelle_VOP_1.01_2025_06_27_FV.pdf (chapter D under "VOP-Prüfergebnis")
 * @see https://febelfin.be/media/pages/publicaties/2023/febelfin-standaarden-voor-online-bankieren/971728b297-1746523070/febelfin-standard-payment-status-report-xml-2025-v1.0-en_final.pdf
 */
enum VopVerificationResult
{
    /** The verification completed and successfully matched the payee information. */
    case CompletedFullMatch;
    /** The verification completed and only partially matched the payee information. */
    case CompletedCloseMatch;
    /** The verification completed but could not match the payee information. */
    case CompletedNoMatch;
    /** The verification completed but not all included transfers were successfully matched. */
    case CompletedPartialMatch;
    /**
     * The verification was attempted but could not be completed. More information MAY be available from
     * {@link VopConfirmationRequest::getVerificationNotApplicableReason()}.
     */
    case NotApplicable;

    /**
     * @param string $codeFromBank The verification status code received from the bank.
     * @return ?VopVerificationResult One of the enum values defined above, or null if the code could not be recognized.
     */
    public static function parse(string $codeFromBank): ?VopVerificationResult
    {
        return match ($codeFromBank) {
            'RCVC' => self::CompletedFullMatch,
            'RVMC' => self::CompletedCloseMatch,
            'RVNM' => self::CompletedNoMatch,
            'RVCM' => self::CompletedPartialMatch,
            'RVNA' => self::NotApplicable,
            default => throw new UnexpectedResponseException("Unexpected VOP result code: $codeFromBank"),
        };
    }
}
