<?php

namespace Genemu\Bundle\FormBundle\Form\Core\Type;

use Symfony\Component\Form\FormBuilder;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Genemu\Bundle\FormBundle\Form\Core\DataTransformer\ValueToStringTransformer;

/**
 * A Form type that just renders the field as a p tag. This is useful for forms where certain field
 * need to be shown but not editable.
 *
 * @author Adam Kuśmierz <adam@kusmierz.be>
 */
class PlainType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function getDefaultOptions(array $options)
    {
        $defaultOptions = array(
            'widget'  => 'field',
            'configs' => array(),
            'read_only' => true,
            'empty_value'  => '',
            'attr' => array(
                'class' => $this->getName()
            )
            //'property_path' => false,
        );

        return array_replace_recursive($defaultOptions, $options);
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilder $builder, array $options)
    {
        $format = null;
        if (isset($options['format'])) {
            $format = $options['format'];
        }
        $builder->appendClientTransformer(new ValueToStringTransformer($format));

        // empty value
        $emptyValue = null;
        if (isset($options['empty_value'])) {
            $emptyValue = $options['empty_value'];
        }

        $builder
            ->setAttribute('empty_value', $emptyValue)
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function buildView(FormView $view, FormInterface $form)
    {

        $view
            ->set('value', (string) $form->getClientData())
            ->set('empty_value', $form->getAttribute('empty_value'));
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
        return 'genemu_plain';
    }
}
