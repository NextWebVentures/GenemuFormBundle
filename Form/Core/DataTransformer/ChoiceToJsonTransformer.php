<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Olivier Chauvel <olivier@generation-multiple.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Genemu\Bundle\FormBundle\Form\Core\DataTransformer;

use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\UnexpectedTypeException;
use Symfony\Component\Form\Extension\Core\ChoiceList\ArrayChoiceList;

/**
 * ChoiceToJsonTransformer
 *
 * @author Olivier Chauvel <olivier@generation-multiple.com>
 */
class ChoiceToJsonTransformer implements DataTransformerInterface
{
    protected $choiceList;
    protected $widget;
    protected $multiple;
    protected $ajax;

    /**
     * Constructs
     *
     * @param ArrayChoiceList $choiceList
     * @param string          $widget
     * @param boolean         $multiple
     * @param boolean         $ajax
     */
    public function __construct(ArrayChoiceList $choiceList, $widget = 'choice', $multiple = false, $ajax = false)
    {
        $this->choiceList = $choiceList;
        $this->multiple = $multiple;
        $this->widget = $widget;
        $this->ajax = $ajax;
    }

    /**
     * {@inheritdoc}
     */
    public function transform($choices)
    {
        if (true === empty($choices)) {
            return;
        }

        if (true === is_scalar($choices)) {
            $choices = array($choices);
        }

        if (false === is_array($choices)) {
            throw new UnexpectedTypeException($choices, 'array');
        }

        if (method_exists($this->choiceList, 'getIntersect')) {
            $json = $this->choiceList->getIntersect($choices);
        } else {
            $json = array();
            foreach ($this->choiceList->getChoices() as $choice) {
                if (true === in_array($choice['value'], $choices, true)) {
                    $json[] = $choice;
                }
            }
        }

        if (false === $this->multiple) {
            $json = current($json);
        }

        return json_encode($json);
    }

    /**
     * {@inheritdoc}
     */
    public function reverseTransform($json)
    {
        $values = json_decode(is_array($json) ? current($json) : $json, true);

        if (true === $this->multiple) {
            $choices = array();
            if (empty($values)) {
                $values = array();
            }

            foreach ($values as $value) {
                if (
                    true === $this->ajax &&
                    false === in_array($this->widget, array('entity', 'document', 'model'), true)
                ) {
                    $choices[$value['value']] = $value['label'];
                } else {
                    $choices[] = $value['value'];
                }
            }
        } elseif (is_array($values) && empty($values)) {
            $choices = null;
        } else if (isset($values['value'])) {
            $choices = $values['value'];
        } else {
            $choices = null;
        }

        return $choices;
    }
}
