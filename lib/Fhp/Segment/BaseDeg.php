<?php

namespace Fhp\Segment;

use Fhp\Syntax\Parser;
use Fhp\Syntax\Serializer;
use Fhp\UnsupportedException;

/**
 * Base class for Data Element Groups (Datenelement-Gruppen; DEGs).
 */
abstract class BaseDeg
{
    /**
     * Reference to the descriptor for this type of segment.
     */
    private ?DegDescriptor $descriptor = null;

    /**
     * @return DegDescriptor The descriptor for this Deg type.
     */
    public function getDescriptor(): DegDescriptor
    {
        if ($this->descriptor === null) {
            $this->descriptor = DegDescriptor::get(static::class);
        }
        return $this->descriptor;
    }

    public function __debugInfo()
    {
        $result = get_object_vars($this);
        unset($result['descriptor']); // Don't include descriptor in debug output, to avoid clutter.
        return $result;
    }

    /**
     * @throws \InvalidArgumentException If any element in this DEG is invalid.
     */
    public function validate()
    {
        $this->getDescriptor()->validateObject($this);
    }

    /**
     * Short-hand for {@link Serializer::serializeDeg()}.
     * @return string The HBCI wire format representation of this DEG.
     */
    public function serialize(): string
    {
        return Serializer::serializeDeg($this, $this->getDescriptor());
    }

    public function __serialize(): array
    {
        return [Serializer::serializeDeg($this, $this->getDescriptor())];
    }

    public function __unserialize(array $serialized): void
    {
        Parser::parseDeg($serialized[0], $this);
    }

    /**
     * Convenience function for {@link Parser::parseGroup()}. This function should not be called on BaseDeg itself, but
     * only on one of its sub-classes.
     * @param string $rawElements The serialized wire format for a data element group.
     * @return static The parsed value.
     */
    public static function parse(string $rawElements): static
    {
        if (static::class === BaseDeg::class) {
            throw new UnsupportedException('Must not call BaseDeg::parse() on the base class');
        }
        return Parser::parseDeg($rawElements, static::class);
    }
}
