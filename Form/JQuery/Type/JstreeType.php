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

/**
 * Jstree to JQueryLib
 *
 * @author Adam Kuśmierz <kusmierz@gmail.com>
 */
class JstreeType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilder $builder, array $options)
    {
        $builder->setAttribute('route_name',            $options['route_name'])
                ->setAttribute('query_param_name',      $options['query_param_name'])
                ->setAttribute('json_transform_func',   $options['json_transform_func']);
    }

    /**
     * {@inheritdoc}
     */
    public function buildView(FormView $view, FormInterface $form)
    {
        $view->set('route_name',            $form->getAttribute('route_name'))
             ->set('query_param_name',      $form->getAttribute('query_param_name'))
             ->set('json_transform_func',   $form->getAttribute('json_transform_func'))
             ->set('config', array(
            'url' => '/categories/list',
            'themes' => '/abczdrowie/bundles/abcadminadmin/css/jquery/jstree/themes/default/style.css'
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function getDefaultOptions(array $options)
    {
        $defaultOptions = array(
            'widget'                => 'choice',

            // for autocomplete: symfony route name
            'route_name'			=> null,
            // for autocomplete: name of GET parameter used to send search term to given route
            'query_param_name'		=> 'term',
            // for autocomplete: javascript function that is used to transform JSON data returned by requests to the
            //                   given route, this default implementation assumes that data returned is in the same format
            //                   as used by the 'jquery_autocomplete form-type' (also defined in the Bundle)
            'json_transform_func'	=> '
            function(data) {
                var terms = {};
                $.each(data, function (k, v) {
                    if (v.value && v.label) terms[v.value] = v.label;
                });
                return terms;
            }'
        );

        return array_replace($defaultOptions, $options);
    }

    /**
     * {@inheritdoc}
     */
    public function getAllowedOptionValues(array $options)
    {
        return array(
            'widget' => array(
                'choice',
                'entity',
                'document',
                'model',
            )
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getParent(array $options)
    {
        return $options['widget'];
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'genemu_jqueryjstree';
    }
}
