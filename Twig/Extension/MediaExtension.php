<?php

/*
 * This file is part of the Sonata Project package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Enemis\SonataMediaLiipImagineBundle\Twig\Extension;

use Doctrine\Bundle\DoctrineBundle\Registry;
use Sonata\MediaBundle\Model\MediaInterface;
use Sonata\MediaBundle\Twig\Extension\MediaExtension as SonataMediaExtension;

class MediaExtension extends SonataMediaExtension
{
    /**
     * Returns the thumbnail for the provided media.
     *
     * @param MediaInterface $media
     * @param string         $format
     * @param array          $options
     *
     * @return string
     */
    public function thumbnail($media, $format, $options = array())
    {
        $media = $this->getMedia($media);

        if (!$media) {
            return '';
        }

        $provider = $this->getMediaService()
           ->getProvider($media->getProviderName());

        $format = $provider->getFormatName($media, $format);

        // build option
        $defaultOptions = array(
            'title' => $media->getName(),
            'alt' => $media->getName(),
        );

        if (method_exists($provider, 'resolveImageBox')) {
            $box = $provider->resolveImageBox($media, $format);
            $defaultOptions['width'] = $box->getWidth();
        }

        $format = $provider->getFormatName($media, $format);
        $format_definition = $provider->getFormat($format);

        if (isset($format_definition['width'])) {
            $defaultOptions['width'] = $format_definition['width'];
        }
        if (isset($format_definition['height'])) {
            $defaultOptions['height'] = $format_definition['height'];
        }

        $options = array_merge($defaultOptions, $options);

        $options['src'] = $provider->generatePublicUrl($media, $format);

        return $this->render($provider->getTemplate('helper_thumbnail'), array(
            'media' => $media,
            'options' => $options,
        ));
    }


    /**
     * @param MediaInterface $media
     * @param string         $format
     *
     * @return string
     */
    public function path($media, $format)
    {
        $media = $this->getMedia($media);

        if (!$media) {
            return '';
        }

        $provider = $this->getMediaService()
           ->getProvider($media->getProviderName());

        $format = $provider->getFormatName($media, $format);

        return $provider->generatePublicUrl($media, $format);
    }

    /**
     * @param mixed $media
     *
     * @return MediaInterface|null|bool
     */
    private function getMedia($media)
    {
        if (!$media instanceof MediaInterfacece && strlen($media) > 0) {
            $media = $this->mediaManager->findOneBy(array(
                'id' => $media,
            ));
        }

        if (!$media instanceof MediaInterface) {
            return false;
        }

        if ($media->getProviderStatus() !== MediaInterface::STATUS_OK) {
            return false;
        }

        return $media;
    }
}
