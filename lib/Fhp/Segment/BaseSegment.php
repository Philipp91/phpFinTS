<?php

namespace Fhp\Segment;

use Fhp\Syntax\Parser;
use Fhp\Syntax\Serializer;

/**
 * Base class for segments. Sub-classes names need to follow the format "<Kennung>v<Version>" where <Kennung> is the
 * type of the segment (e.g. "HITANS") and <Version> is the numeric version. The *public* member fields of a sub-class
 * determine the structure of the segment. The order matters for the wire format, whereas the field names are only used
 * for documentation/readability purposes within this library. See {@link HITANSv6} for an example of a sub-class.
 */
abstract class BaseSegment implements SegmentInterface
{
    /** Reference to the descriptor for this type of segment. */
    private ?SegmentDescriptor $descriptor = null;
    public Segmentkopf $segmentkopf;

    /**
     * @return SegmentDescriptor The descriptor for this segment's type.
     */
    public function getDescriptor(): SegmentDescriptor
    {
        if ($this->descriptor === null) {
            $this->descriptor = SegmentDescriptor::get(static::class);
        }
        return $this->descriptor;
    }

    public function getName(): string
    {
        return $this->segmentkopf->segmentkennung;
    }

    public function getVersion(): int
    {
        return $this->segmentkopf->segmentversion;
    }

    public function getSegmentNumber(): int
    {
        return $this->segmentkopf->segmentnummer;
    }

    /**
     * @param int $segmentNumber The new segment number.
     * @return $this The same instance.
     */
    public function setSegmentNumber(int $segmentNumber): static
    {
        $this->segmentkopf->segmentnummer = $segmentNumber;
        return $this;
    }

    /**
     * @throws \InvalidArgumentException If any element in this segment is invalid.
     */
    public function validate()
    {
        $this->getDescriptor()->validateObject($this);
    }

    /**
     * Short-hand for {@link Serializer::serializeSegment()}.
     * @return string The HBCI wire format representation of this segment, in ISO-8859-1 encoding, terminated by the
     *     segment delimiter.
     */
    public function serialize(): string
    {
        return Serializer::serializeSegment($this);
    }

    public function __serialize(): array
    {
        return [$this->serialize()];
    }

    public function __unserialize(array $serialized): void
    {
        Parser::parseSegment($serialized[0], $this);
    }

    public function __toString(): string
    {
        return $this->serialize();
    }

    public function __debugInfo()
    {
        $result = get_object_vars($this);
        unset($result['descriptor']); // Don't include descriptor in debug output, to avoid clutter.
        return $result;
    }

    /**
     * Convenience function for {@link Parser::parseSegment()}.
     * @param string $rawSegment The serialized wire format for a single segment (segment delimiter must be present at
     *     the end). This should be ISO-8859-1-encoded.
     * @return static The parsed segment.
     */
    public static function parse(string $rawSegment): static
    {
        if (static::class === BaseSegment::class) {
            // Called as BaseSegment::parse(), so we need to determine the right segment type/class.
            return Parser::detectAndParseSegment($rawSegment);
        } else {
            // The parse() function was called on the segment subclass itself.
            return Parser::parseSegment($rawSegment, static::class);
        }
    }

    /**
     * @return static A new segment of the type on which this function was called, with the Segmentkopf initialized.
     */
    public static function createEmpty(): static
    {
        if (static::class === BaseSegment::class) {
            throw new \InvalidArgumentException('Must not call BaseSegment::createEmpty() on the super class');
        }
        $result = new static();
        $descriptor = $result->getDescriptor();
        $result->segmentkopf = new Segmentkopf();
        $result->segmentkopf->segmentkennung = $descriptor->kennung;
        $result->segmentkopf->segmentversion = $descriptor->version;
        return $result;
    }
}
