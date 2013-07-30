<?php

namespace Genemu\Bundle\FormBundle\Form\Core\Type;

use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\Form\Extension\Core\DataMapper\PropertyPathMapper;

/**
 * A Form type that just renders the field as a p tag. This is useful for forms where certain field
 * need to be shown but not editable.
 *
 * @author Adam Kuśmierz <adam@kusmierz.be>
 */
class SubFormType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $options = $this->getDefaultOptions($options);

        foreach ($options['compound'] as $name => $config) {
            $type = (isset($config['type']) ? $config['type'] : null);
            $option = (isset($config['options']) ? $config['options'] : null);

            $builder->add($name, $type, (array) $option);
        }

        //$builder->setAttribute('configs', $options['configs']);
    }

    /**
     * {@inheritdoc}
     */
    public function buildView(FormView $view, FormInterface $form, array $options)
    {}

    /**
     * {@inheritdoc}
     */
    public function getDefaultOptions(array $options)
    {
        $defaultOptions = array(
            'required' => false,
        );

        if (isset($options['data_class']) && empty($options['data_class'])) {
            $defaultOptions['empty_data'] = array();
        }

        return array_replace_recursive($defaultOptions, $options);

    }

    /**
     * {@inheritdoc}
     */
    public function getParent()
    {
        return 'form';
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'genemu_subform';
    }
}
