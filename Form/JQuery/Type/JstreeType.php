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
                ->setAttribute('query_param_name',      $options['query_param_name']);
    }

    /**
     * {@inheritdoc}
     */
    public function buildView(FormView $view, FormInterface $form)
    {
        $view->set('route_name',            $form->getAttribute('route_name'))
             ->set('query_param_name',      $form->getAttribute('query_param_name'))
             ->set('config', array(
            'list' => 'AbcAdminCategoriesBundle_ListController_json',
            'search' => 'AbcAdminCategoriesBundle_SearchController_nodePathById_json',
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
            'query_param_name'		=> 'term'
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
