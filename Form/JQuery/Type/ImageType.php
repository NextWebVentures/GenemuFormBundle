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
use Symfony\Component\HttpFoundation\File\File;

use Genemu\Bundle\FormBundle\Gd\File\Image;

/**
 * ImageType
 *
 * @author Olivier Chauvel <olivier@generation-multiple.com>
 */
class ImageType extends AbstractType
{
    private $selected;
    private $thumbnails;
    private $filters;

    /**
     * Constructs
     *
     * @param string $selected
     * @param array  $thumbnails
     * @param array  $filters
     */
    public function __construct($selected = null, array $thumbnails = array(), array $filters = array())
    {
        $this->selected = $selected;
        $this->thumbnails = $thumbnails;
        $this->filters = $filters;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilder $builder, array $options)
    {
        if (isset($options['thumbnails']) && !empty($options['thumbnails']) && is_array($options['thumbnails'])) {
            $this->thumbnails = $options['thumbnails'];
        }

        if (isset($options['selected']) && isset($this->thumbnails[$options['selected']])) {
            $this->selected = $options['selected'];
        }

        if (isset($options['filters']) && !empty($options['filters']) && is_array($options['filters'])) {
            $this->filters = $options['filters'];
        }
    }

    /**
     * {@inheritdoc}
     */
    public function buildView(FormView $view, FormInterface $form)
    {
        $configs = $form->getAttribute('configs');
        $data = $form->getClientData();

        if (!empty($data)) {
            if (false === ($data instanceof Image)) {
                if ($data instanceof File) {
                    $path = $data->getPath();
                    if (empty($path)) {
                        $path = $form->getAttribute('rootDir') . '/' . $configs['folder'];
                    }
                    $image = new Image($path . '/' . $data->getFilename());
                } else {
                    $data = (string) $data;
                    if (!empty($data)) {
                        $image = new Image($form->getAttribute('rootDir') . '/' . $data);
                    } else {
                        $image = null;
                    }
                }
            }
        }

        if (!empty($image)) {
            $image->searchThumbnails();

            if (($configs['custom_storage_folder']) && (false === ($value = $form->getClientData()) instanceof File)) {
                // This if will be executed only when we load entity with existing file pointed to the folder different
                // from $configs['folder']
                $folder = dirname($value);
            } else {
                $folder = $data->getPath();
                if (empty($folder)) {
                    $folder = $configs['folder'];
                }
            }

            if (true === $image->hasThumbnail($this->selected)) {
                $thumbnail = $image->getThumbnail($this->selected);

                $view
                    ->set('thumbnail', array(
                        'file' => $folder . DIRECTORY_SEPARATOR . $thumbnail->getFilename(),
                        'width' => $thumbnail->getWidth(),
                        'height' => $thumbnail->getHeight(),
                    ));
            }

            $value = $folder . DIRECTORY_SEPARATOR . $image->getFilename();

            $view
                ->set('value', $value)
                ->set('file', $value)
                ->set('width', $image->getWidth())
                ->set('height', $image->getHeight());
        }

        $view->set('filters', $this->filters);
        $view->set('thumbnails', $this->thumbnails);
        $view->set('selected', $this->selected);
    }

    /**
     * {@inheritdoc}
     */
    public function getDefaultOptions(array $options)
    {
        $defaultOptions = array(
            'configs' => array(
                'fileExt'  => '*.jpg;*.gif;*.png;*.jpeg',
                'fileDesc' => 'Web Image Files (.jpg, .gif, .png, .jpeg)',
                'auto'     => true,
            )
        );

        return array_replace_recursive($defaultOptions, $options);
    }

    /**
     * {@inheritdoc}
     */
    public function getParent(array $options)
    {
        return 'genemu_jqueryfile';
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'genemu_jqueryimage';
    }
}
