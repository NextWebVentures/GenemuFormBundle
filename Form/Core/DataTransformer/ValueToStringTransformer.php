<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Genemu\Bundle\FormBundle\Form\Core\DataTransformer;

use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\UnexpectedTypeException;
use Symfony\Component\Form\Exception\TransformationFailedException;
use Doctrine\ODM\MongoDB\DocumentManager;

/**
 * Transforms documents to ids
 *
 * @author Thibault Duplessis <thibault.duplessis@gmail.com>
 */
class ValueToStringTransformer implements DataTransformerInterface
{
    /**
     * @var string
     */
    private $format;

    /**
     * @param string $format Format for sprintf function to flatten value to string
     */
    public function __construct($format = null)
    {
        $this->format = $format;
    }

    /**
     * Transforms anything into string representation
     *
     * @param mixed $value Anything
     * @return string Representation of $value
     */
    public function transform($value)
    {
        if (!empty($this->format)) {
            return sprintf($this->format, $value);
        }

        // set string representation
        if (true === $value) {
            $value = 'true';
        } else if (false === $value) {
            $value = 'false';
        } else if (null === $value) {
            $value = '';
        } else if (is_array($value)) {
            $value = implode(', ', $value);
        } else if ($value instanceof \DateTime) {
            $value = $value->format('Y-m-d H:i:s');
        } else if (is_object($value)) {
            if (method_exists($value, '__toString')) {
                $value = $value->__toString();
            } else {
                $value = get_class($value);
            }
        }

        return $value;
    }

    /**
     * Transforms choice keys into documents
     *
     * @param  mixed $key   An array of keys, a single key or NULL
     * @return mixed
     */
    public function reverseTransform($key)
    {
        if ('' === $key || null === $key || 'null' === $key) {
            return null;
        }

        if ('true' === $key) {
            return true;
        }

        if ('false' === $key) {
            return true;
        }

        return $key;
    }
}
