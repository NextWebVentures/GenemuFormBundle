<?php

/**
 * This file is part of the Symfony package.
 *
 * (c) Olivier Chauvel <olivier@generation-multiple.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Genemu\Bundle\FormBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\File\Exception\FileException;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;

use Genemu\Bundle\FormBundle\Gd\File\Image;

/**
 * Class ImageController
 *
 * @author Olivier Chauvel <olivier@generation-multiple.com>
 */
class ImageController extends Controller
{
    /**
     * @Route("/genemu_change_image", name="genemu_form_image")
     */
    public function changeAction(Request $request)
    {
        $rootDir = rtrim($this->container->getParameter('genemu.form.file.root_dir'), '/\\') . DIRECTORY_SEPARATOR;
        $folder = rtrim($this->container->getParameter('genemu.form.file.folder'), '/\\') . DIRECTORY_SEPARATOR;
        $uploadDir = rtrim($this->container->getParameter('genemu.form.file.upload_dir'), '/\\') . DIRECTORY_SEPARATOR;

        $file = $request->get('image');

        $handle = new Image($rootDir . $this->stripQueryString($file));

        // if custom_storage_folder is true, treat "folder" as tmp dir - copy changed file there
        // and don't overwrite by now (it should be done after "Save" button action)
        if ($this->container->hasParameter('genemu.form.file.custom_storage_folder') &&
            // but change oryginal if we are already in $folder
            trim($folder, '/\\') != trim(substr($handle->getPath(), strlen($rootDir)), '/\\')
        ) {
            if ($this->container->hasParameter('genemu.form.file.disable_guess_extension')) {
                $name = uniqid() . '.' . $handle->getExtension();
            } else {
                $name = uniqid() . '.' . $handle->guessExtension();
            }

            $target = $uploadDir . $name;
            if (!@copy($handle->getPathname(), $target)) {
                $error = error_get_last();
                throw new FileException(sprintf('Could not move the file "%s" to "%s" (%s)', $handle->getPathname(), $target, strip_tags($error['message'])));
            }

            $handle = new Image($target);
        }

        switch ($request->get('filter')) {
            case 'rotate':
                $handle->addFilterRotate(90);
                break;
            case 'negative':
                $handle->addFilterNegative();
                break;
            case 'bw':
                $handle->addFilterBw();
                break;
            case 'sepia':
                $handle->addFilterSepia('#C68039');
                break;
            case 'crop':
                $x = $request->get('x');
                $y = $request->get('y');
                $w = $request->get('w');
                $h = $request->get('h');

                $handle->addFilterCrop($x, $y, $w, $h);
                break;
            case 'blur':
                $handle->addFilterBlur();
                break;
            default:
                break;
        }

        $handle->save();
        $thumbnail = $handle;

        if (true === $this->container->hasParameter('genemu.form.image.thumbnails')) {
            $thumbnails = $this->container->getParameter('genemu.form.image.thumbnails');

            foreach ($thumbnails as $name => $thumbnail) {
                $handle->createThumbnail($name, $thumbnail[0], $thumbnail[1]);
            }

            $selected = key(reset($thumbnails));
            if ($this->container->hasParameter('genemu.form.image.selected')) {
                $selected = $this->container->getParameter('genemu.form.image.selected');
            }

            $thumbnail = $handle->getThumbnail($selected);
        }

        $filePath = $folder . $handle->getFilename();

        $json = array(
            'result' => '1',
            'file' => $filePath . '?' . time(),
            'thumbnail' => array(
                'file' => $filePath . '?' . time(),
                'width' => $thumbnail->getWidth(),
                'height' => $thumbnail->getHeight()
            ),
            'image' => array(
                'width' => $handle->getWidth(),
                'height' => $handle->getHeight()
            )
        );

        return new Response(json_encode($json));
    }

    /**
     * Delete info after `?`
     *
     * @param string $file
     *
     * @return string
     */
    private function stripQueryString($file)
    {
        if (false !== ($pos = strpos($file, '?'))) {
            $file = substr($file, 0, $pos);
        }

        return $file;
    }
}
