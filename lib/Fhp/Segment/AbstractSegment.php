<?php

namespace Fhp\Segment;

/** @deprecated TODO Remove */
abstract class AbstractSegment implements SegmentInterface
{
    const SEGMENT_SEPARATOR = "'";
    const DEFAULT_COUNTRY_CODE = 280;

    /** @var string */
    protected $type;
    /** @var int */
    protected $segmentNumber;
    /** @var int */
    protected $version;
    /** @var array */
    protected $dataElements;

    /**
     * AbstractSegment constructor.
     *
     * @param $type
     * @param $segmentNumber
     * @param $version
     */
    public function __construct($type, $segmentNumber, $version, array $dataElements = [])
    {
        $this->type = strtoupper($type);
        $this->version = $version;
        $this->segmentNumber = $segmentNumber;
        $this->dataElements = $dataElements;
    }

    public function setDataElements(array $dataElements = [])
    {
        $this->dataElements = $dataElements;
    }

    /**
     * @return array
     */
    public function getDataElements()
    {
        return $this->dataElements;
    }

    /**
     * @return string
     */
    public function toString()
    {
        $string = $this->type . ':' . $this->segmentNumber . ':' . $this->version;

        foreach ($this->dataElements as $de) {
            $string .= '+' . (string) $de;
        }

        if ($string == '') {
            return $string;
        }

        return $string . static::SEGMENT_SEPARATOR;
    }

    public function __toString(): string
    {
        return $this->toString();
    }

    /**
     * @return string
     */
    public function humanReadable(bool $translateCodes = false)
    {
        return str_replace(
            ["'", '+'],
            [PHP_EOL, PHP_EOL . '  '],
            $translateCodes
                ? NameMapping::translateResponse($this->toString())
                : $this->toString()
        );
    }

    public function getSegmentNumber(): int
    {
        return $this->segmentNumber;
    }

    public function getVersion(): int
    {
        return $this->version;
    }
}
