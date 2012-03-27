<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Olivier Chauvel <olivier@generation-multiple.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Genemu\Bundle\FormBundle\Form\JQuery\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilder;
use Symfony\Component\Form\FormView;
use Symfony\Component\Form\FormInterface;
use Doctrine\ODM\MongoDB\DocumentManager;
use Gedmo\Tool\Wrapper\MongoDocumentWrapper;
use Genemu\Bundle\FormBundle\Form\Core\DataTransformer\DocumentToIdTransformer;

/**
 * Jstree to JQueryLib
 *
 * @author Adam Kuśmierz <kusmierz@gmail.com>
 */
class JstreeType extends AbstractType
{
    /**
     * The field of which the identifier of the underlying class consists
     *
     * This property should only be accessed through identifier.
     *
     * @var string
     */
    private $identifier;

    /**
     * DocumentManager
     *
     * @var \Doctrine\ODM\MongoDB\DocumentManager
     */
    private $documentManager;

    public function __construct(DocumentManager $documentManager)
    {
        $this->documentManager = $documentManager;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilder $builder, array $options)
    {
        $builder->prependClientTransformer(new DocumentToIdTransformer($options['document_manager'], $options['class']));

        $builder->setAttribute('config',            $options['config']);
        //        ->setAttribute('query_param_name',      $options['query_param_name']);
    }

    /**
     * {@inheritdoc}
     */
    public function buildView(FormView $view, FormInterface $form)
    {
        $config = $form->getAttribute('config');

        if (!isset($config['document_id'])) {
            $form = $form->getParent();
            $normData = $form->getNormData();
            $wrappedNormData = MongoDocumentWrapper::wrapp($normData, $this->documentManager);
            $config['document_id'] = $wrappedNormData->getIdentifier(true);
        }

        $view
            ->set('config', $config);
    }

    /**
     * {@inheritdoc}
     */
    public function getDefaultOptions(array $options)
    {
        $defaultOptions = array(
            'class'             => null,
            'document_manager'  => $this->documentManager
        );

        return array_replace($defaultOptions, $options);
    }

    /**
     * {@inheritdoc}
     */
    public function getParent(array $options)
    {
        return 'field';
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'genemu_jqueryjstree';
    }
}
