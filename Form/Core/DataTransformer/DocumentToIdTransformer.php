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
class DocumentToIdTransformer implements DataTransformerInterface
{
    /**
     * @var \Doctrine\ODM\MongoDB\DocumentManager
     */
    private $dm;
    /**
     * @var string
     */
    private $class;

    public function __construct(DocumentManager $dm, $class)
    {
        $this->dm = $dm;
        $this->class = $class;
    }

    /**
     * Transforms documents into choice keys
     *
     * @param Collection|object $document A collection of documents, a single document or NULL
     * @return mixed An array of choice keys, a single key or NULL
     */
    public function transform($document)
    {
        if (empty($document)) {
            return $document;
        }

        if (!is_object($document)) {
            throw new UnexpectedTypeException($document, 'object');
        }

        if ($this->dm->getUnitOfWork()->isInIdentityMap($document)) {
            $document = $this->dm->getUnitOfWork()->getDocumentIdentifier($document);
        } else if ($document instanceof \Doctrine\Common\Collections\Collection) {
            /** @var \Doctrine\ODM\MongoDB\PersistentCollection $document */
            $keys = array();
            foreach ($document->getValues() as $d) {
                $keys[] = $this->dm->getUnitOfWork()->getDocumentIdentifier($d);
            }
            $document = $keys;
        } else {
            throw new TransformationFailedException('Document passed to the choice field have to be managed');
        }

        return $document;
    }

    /**
     * Transforms choice keys into documents
     *
     * @param  mixed $key   An array of keys, a single key or NULL
     * @return Collection|object  A collection of documents, a single document
     *                            or NULL
     */
    public function reverseTransform($key)
    {
        if ('' === $key || null === $key) {
            return null;
        }

        // multiple
        $multiple = true;
        if (!is_array($key)) {
            $multiple = false;
            $key = array($key);
        }

        $documents = array();
        foreach ($key as $k) {
            if (!($documents[] = $this->dm->find($this->class, $k))) {
                throw new TransformationFailedException(sprintf('The document with key "%s" could not be found', $k));
            }
        }

        if (!$multiple) {
            return reset($documents);
        }

        return $documents;
    }
}
