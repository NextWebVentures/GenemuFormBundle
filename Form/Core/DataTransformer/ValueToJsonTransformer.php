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
class ValueToJsonTransformer implements DataTransformerInterface
{
    public function __construct()
    {
    }

    /**
     * Transforms anything into json representation
     *
     * @param mixed $value Anything
     * @return string Representation of $value
     */
    public function transform($value)
    {
        if (empty($value)) {
            return null;
        }

        if (is_scalar($value)) {
            return $value;
        }
        return json_encode($value);
    }

    /**
     * Transforms choice keys into documents
     *
     * @param  mixed $key   An array of keys, a single key or NULL
     * @return mixed
     */
    public function reverseTransform($key)
    {
        return json_decode($key);
    }
}
