<?php

namespace Fhp\Response;

use Fhp\FinTsInternal;
use Fhp\Message\AbstractMessage;
use Fhp\Segment;
use Fhp\Segment;
use Fhp\Segment\NameMapping;
use Fhp\Segment\SegmentInterface;
use Fhp\Syntax\Delimiter;
use Fhp\Syntax\Parser;

/**
 * @deprecated TODO Remove
 */
class Response
{
    /** @var string */
    public $rawResponse;

    /** @var string */
    protected $response;

    /** @var Segment\BaseSegment[]|Segment\AbstractSegment[] $segments */
    protected $segments = [];

    /** @var @deprecated string[] $segments */
    protected $rawSegments = [];

    /** @var string */
    protected $dialogId;

    /** @var string */
    protected $systemId;

    protected $dialog = null;

    /**
     * Response constructor.
     *
     * @param string $rawResponse
     */
    public function __construct($rawResponse, \Fhp\Dialog\Dialog $dialog = null)
    {
        if ($rawResponse instanceof Response) {
            $rawResponse = $rawResponse->rawResponse;
        }

        $this->rawResponse = $rawResponse;
        $this->response = $this->unwrapEncryptedResponse($rawResponse);
        $this->segments = Parser::parseSegments($this->response);

        // Compatibility implementation for "findSegments"
        $this->rawSegments = Parser::parseRawSegments($this->response);

        $this->dialog = $dialog;
    }

    /**
     * @return bool
     *
     * @link https://www.hbci-zka.de/dokumente/spezifikation_deutsch/fintsv3/FinTS_3.0_Security_Sicherheitsverfahren_PINTAN_2018-02-23_final_version.pdf
     * Seite: 28
     * Da die Eingabe einer TAN erforderlich ist, erfolgt eine Zwischenspeicherung des Auftrags.
     * Anschließend wird auf Institutsseite eine verfahrensspezifische Challenge ermittelt
     * und dem Kundenprodukt im Segment HITAN mitgeteilt. In
     * HITAN erfolgt die Belegung ebenfalls gemäß TANProzess=4. Durch RM-Code 0030 zusammen mit den Informationen „Auftragsreferenz“ und „Challenge“ aus HITAN
     * erhält das Kundenprodukt in der Kreditinstitutsantwort die
     * Information, dass der Kunde nun auf Basis der Challenge in
     * vereinbarter Form eine TAN ermitteln muss.
     */
    public function isStrongAuthRequired()
    {
        /** @var Segment\TAN\HITANv6 $seg */
        $seg = $this->getSegment('HITAN');

        // Wenn eine zwischengespeicherter Aufrag nach TAN-Übermittlung beantwortet wird, enthält die Antwort
        // auch wieder das HITAN Segment mit der betreffenden Auftragsreferenz, aber ohne Challenge und mit TAN-Modus = 2
        // Dies darf hier nicht als isStrongAuthRequired ausgelegt werden
        return $seg != null && $seg->getTanProzess() == 4 && $seg->getChallenge() != 'nochallenge';
    }

    public function isTANRequest()
    {
        return get_class($this) == "Fhp\Response\GetTANRequest";
    }

    /**
     * @return \Fhp\Dialog\Dialog|null
     */
    public function getDialog()
    {
        return $this->dialog;
    }

    /**
     * Extracts dialog ID from response.
     *
     * @return string|null
     * @throws \Exception
     */
    public function getDialogId()
    {
        /** @var Segment\HNHBK\HNHBKv3 $segment */
        $segment = $this->getSegment('HNHBK');

        if (null === $segment) {
            throw new \RuntimeException('Could not find element HNHBK. Invalid response?');
        }

        return $segment->dialogId;
    }

    /**
     * Extracts bank name from response.
     *
     * @return string|null
     */
    public function getBankName()
    {
        $bankName = null;

        $segment = $this->findSegment('HIBPA');
        if (null != $segment) {
            $split = $this->splitSegment($segment);
            if (isset($split[3])) {
                $bankName = $split[3];
            }
        }

        return $bankName;
    }

    /**
     * Some kind of HBCI pagination.
     *
     * @return array
     */
    public function getTouchDowns(AbstractMessage $message)
    {
        $touchdown = [];
        $messageSegments = $message->getEncryptedSegments();
        /** @var SegmentInterface $msgSeg */
        foreach ($messageSegments as $msgSeg) {
            $segment = $this->findSegmentForReference('HIRMS', $msgSeg);
            if (null != $segment) {
                $parts = $this->splitSegment($segment);
                // remove header
                array_shift($parts);
                foreach ($parts as $p) {
                    $pSplit = $this->splitDeg($p);
                    if ($pSplit[0] == 3040) {
                        $td = $pSplit[3];
                        $touchdown[$msgSeg->getName()] = $td;
                    }
                }
            }
        }

        return $touchdown;
    }

    /**
     * Extracts supported TAN mechanisms from response.
     *
     * @return array
     */
    public function getSupportedTanMechanisms()
    {
        $gv = new GetVariables($this->response);
        return $gv->getSupportedTanMechanisms();
    }

    /**
     * @return int
     */
    public function getHksalMaxVersion()
    {
        return $this->getSegmentMaxVersion('HISALS');
    }

    /**
     * @return int
     */
    public function getHkkazMaxVersion()
    {
        return $this->getSegmentMaxVersion('HIKAZS');
    }

    /**
     * Checks if request / response was successful.
     *
     * @return bool
     */
    public function isSuccess()
    {
        $summary = $this->getMessageSummary();

        foreach ($summary as $code => $message) {
            if ('9' == substr($code, 0, 1)) {
                return false;
            }
        }

        $summary = $this->getSegmentSummary();

        foreach ($summary as $code => $message) {
            if ('9' == substr($code, 0, 1)) {
                return false;
            }
        }

        return true;
    }

    /**
     * @return array
     * @throws \Exception
     */
    public function getMessageSummary()
    {
        return $this->getSummaryBySegment('HIRMG');
    }

    /**
     * @return array
     * @throws \Exception
     */
    public function getSegmentSummary()
    {
        return $this->getSummaryBySegment('HIRMS');
    }

    /**
     * @param string $name
     *
     * @return array
     * @throws \Exception
     */
    protected function getSummaryBySegment($name)
    {
        if (!in_array($name, ['HIRMS', 'HIRMG'])) {
            throw new \Exception('Invalid segment for message summary. Only HIRMS and HIRMG supported');
        }

        $result = [];
        foreach ($this->findSegments($name) as $segment) {
            $segment = $this->splitSegment($segment);
            array_shift($segment);
            foreach ($segment as $de) {
                $de = $this->splitDeg($de);
                $result[$de[0]] = $de[2];
                if (count($de) > 3) {
                    $result[$de[0]] .= ' (';
                    for ($ii = 3; $ii < count($de); ++$ii) {
                        $result[$de[0]] .= $de[$ii];
                        if ($ii !== count($de) - 1) {
                            $result[$de[0]] .= ', ';
                        }
                    }
                    $result[$de[0]] .= ')';
                }
            }
        }

        return $result;
    }

    /**
     * @param string $segmentName
     *
     * @return int
     */
    public function getSegmentMaxVersion($segmentName)
    {
        $version = 3;
        $segments = $this->findSegments($segmentName);
        foreach ($segments as $s) {
            $parts = $this->splitSegment($s);
            $segmentHeader = $this->splitDeg($parts[0]);
            $curVersion = (int) $segmentHeader[2];
            if ($curVersion > $version) {
                $version = $curVersion;
            }
        }

        return $version;
    }

    /**
     * @return string
     * @throws \Exception
     */
    public function getSystemId()
    {
        $segment = $this->findSegment('HISYN');

        if (!preg_match('/HISYN:\d+:\d+:\d+\+(.+)/', $segment, $matches)) {
            throw new \Exception('Could not determine system id.');
        }

        return FinTsInternal::unescapeString($matches[1]);
    }

    /**
     * @param bool $translateCodes
     *
     * @return string
     */
    public function humanReadable($translateCodes = false)
    {
        return str_replace(
            ["'", '+'],
            [PHP_EOL, PHP_EOL . '  '],
            $translateCodes
                ? NameMapping::translateResponse($this->rawResponse)
                : $this->rawResponse
        );
    }

    /**
     * @param string           $name
     *
     * @return string|null
     */
    protected function findSegmentForReference($name, SegmentInterface $reference)
    {
        $segments = $this->findSegments($name);
        foreach ($segments as $seg) {
            $segSplit = $this->splitSegment($seg);
            $segSplit = array_shift($segSplit);
            $segSplit = $this->splitDeg($segSplit);
            if ($segSplit[3] == $reference->getSegmentNumber()) {
                return $seg;
            }
        }

        return null;
    }

    /**
     * @deprecated use getSegment
     *
     * @param string $name
     * @return string|null
     */
    public function findSegment($name)
    {
        return $this->findSegments($name, true);
    }

    /**
     * @deprecated use getSegments
     *
     * @param string $name
     * @param bool   $one
     *
     * @return array|string|null
     */
    protected function findSegments($name, $one = false)
    {
        $found = $one ? null : [];

        foreach ($this->rawSegments as $segment) {
            $split = explode(':', $segment, 2);

            $segment = $this->conformToUtf8($segment);

            if ($split[0] == $name) {
                if ($one) {
                    return $segment;
                }
                $found[] = $segment;
            }
        }

        return $found;
    }

    /**
     * @param string $name
     * @return Segment\BaseSegment|Segment\AbstractSegment|null
     */
    protected function getSegment($name)
    {
        $segments = $this->getSegments($name);
        if (count($segments) > 0) {
            return $segments[0];
        }

        return null;
    }

    /**
     * @param string $name
     * @return Segment\BaseSegment[]|Segment\AbstractSegment[]
     */
    protected function getSegments($name)
    {
        $result = [];

        foreach ($this->segments as $segment) {
            if ($segment->getName() == $name) {
                $result[] = $segment;
            }
        }

        return $result;
    }

    /** @deprecated only used with the deprecated Response::findSegments */
    protected function conformToUtf8($string)
    {
        return iconv('ISO-8859-1', 'UTF-8', $string);
    }

    /**
     * @deprecated does not work if a segment contains binary data
     *
     * @param $segment
     *
     * @return array
     */
    public function splitSegment($segment, $fix = true)
    {
        preg_match("@\<\?xml.+Document\>@", $segment, $matches);
        $segment = preg_replace("@\<\?xml.+Document\>@", 'EXTRACTEDXML', $segment);

        $parts = preg_split('/\+(?<!\?\+)/', $segment);

        foreach ($parts as &$part) {
            if ($fix) {
                $part = str_replace('?+', '+', $part);
            }
            if (trim($part) != '' and strpos($part, 'EXTRACTEDXML') > 0 and isset($matches[0])) {
                $part = str_replace('EXTRACTEDXML', $matches[0], $part);
            }
        }

        return $parts;
    }

    /**
     * @param $deg
     *
     * @return array
     */
    protected function splitDeg($deg)
    {
        return explode(':', $deg);
    }

    /**
     * @deprecated does not work, if the segment contains binary data
     *
     * @param int $idx
     * @param     $segment
     *
     * @return string|null
     */
    protected function getSegmentIndex($idx, $segment)
    {
        $segment = $this->splitSegment($segment);
        if (isset($segment[$idx - 1])) {
            return $segment[$idx - 1];
        }

        return null;
    }

    /**
     * Replaces the segment HNVSD itself by the payload
     *
     * @param string $response
     * @return string
     */
    private function unwrapEncryptedResponse($response)
    {
        if (preg_match('/(HNVSD:\d+:\d+\+' . Delimiter::BINARY . '(\d+)' . Delimiter::BINARY . ')/', $response, $matches, PREG_OFFSET_CAPTURE) === 1) {
            // 0 -> HNVSD begin
            $result = substr($response, 0, $matches[1][1]);

            // HNVSD Payload
            $length = $matches[2][0];
            $start = $matches[1][1] + strlen($matches[1][0]);

            $result .= substr($response, $start, $length);

            // HNVSD End -> End
            $result .= substr($response, $start + $length + 1); // + 1 = Message delimiter "'"

            return $result;
        }

        return $response;
    }
}
