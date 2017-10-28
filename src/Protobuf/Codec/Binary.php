<?php

declare(strict_types=1);

namespace Bip70\Protobuf\Codec;

use DrSlump\Protobuf;
use DrSlump\Protobuf\Codec\Binary\Writer as BinaryWriter;
use DrSlump\Protobuf\Codec\Binary as BinaryCodec;

class Binary extends BinaryCodec
{
    /**
     * @var bool
     */
    protected static $discardValuesMatchingDefault = false;

    /**
     * @param bool $setting
     */
    public static function setDiscardValuesMatchingDefault(bool $setting)
    {
        static::$discardValuesMatchingDefault = $setting;
    }

    /**
     * @param Protobuf\Message $message
     * @return string
     */
    protected function encodeMessage(Protobuf\Message $message)
    {
        $writer = new BinaryWriter();

        // Get message descriptor
        $descriptor = Protobuf::getRegistry()->getDescriptor($message);

        foreach ($descriptor->getFields() as $tag => $field) {
            $empty = !$message->_has($tag);
            if ($field->isRequired() && $empty) {
                throw new \UnexpectedValueException(
                    'Message ' . get_class($message) . '\'s field tag ' . $tag . '(' . $field->getName() . ') is required but has no value'
                );
            }

            // Skip empty fields
            if ($empty) {
                continue;
            }

            $type = $field->getType();
            $wire = $field->isPacked() ? self::WIRE_LENGTH : $this->getWireType($type, null);

            // Compute key with tag number and wire type
            $key = $tag << 3 | $wire;

            $value = $message->_get($tag);
            if (self::$discardValuesMatchingDefault) {
                if ($field->hasDefault() && ($value === $field->getDefault())) {
                    continue;
                }
            }

            if ($field->isRepeated()) {
                // Packed fields are encoded as a length-delimited stream containing
                // the concatenated encoding of each value.
                if ($field->isPacked() && !empty($value)) {
                    $subwriter = new BinaryWriter();
                    foreach ($value as $val) {
                        $this->encodeSimpleType($subwriter, $type, $val);
                    }
                    $data = $subwriter->getBytes();
                    $writer->varint($key);
                    $writer->varint(strlen($data));
                    $writer->write($data);
                } else {
                    // Make sure the value is an array of values
                    $value = is_array($value) ? $value : array($value);
                    foreach ($value as $val) {
                        // Skip nullified repeated values
                        if (null === $val) {
                            continue;
                        } else if ($type !== Protobuf::TYPE_MESSAGE) {
                            $writer->varint($key);
                            $this->encodeSimpleType($writer, $type, $val);
                        } else {
                            $writer->varint($key);
                            $data = $this->encodeMessage($val);
                            $writer->varint(strlen($data));
                            $writer->write($data);
                        }
                    }
                }
            } else if ($type !== Protobuf::TYPE_MESSAGE) {
                $writer->varint($key);
                $this->encodeSimpleType($writer, $type, $value);
            } else {
                $writer->varint($key);
                $data = $this->encodeMessage($value);
                $writer->varint(strlen($data));
                $writer->write($data);
            }
        }

        return $writer->getBytes();
    }
}
