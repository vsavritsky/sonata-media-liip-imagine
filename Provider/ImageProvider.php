<?php

namespace Enemis\SonataMediaLiipImagineBundle\Provider;

use Sonata\MediaBundle\Model\MediaInterface;
use \Sonata\MediaBundle\Provider\ImageProvider as SonataImageProvider;

class ImageProvider extends SonataImageProvider
{
    use ProviderOverrideTrait;

    /**
     * {@inheritdoc}
     */
    public function getHelperProperties(MediaInterface $media, $format, $options = array())
    {
        $box = $this->resolveImageBox($media, $format);

        $mediaWidth = $box->getWidth();

        $params = array(
            'alt' => $media->getName(),
            'title' => $media->getName(),
            'src' => $this->generatePublicUrl($media, $format),
            'width' => $mediaWidth,
            'height' => $box->getHeight(),
            'sizes' => '',
            'srcset' => '',
        );

        return array_merge($params, $options);
    }

    /**
     * {@inheritdoc}
     */
    public function getReferenceImage(MediaInterface $media)
    {
        return sprintf(
            '%s/%s',
            $this->generatePath($media),
            $media->getProviderReference()
        );
    }
}
