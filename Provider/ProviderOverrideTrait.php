<?php

namespace Enemis\SonataMediaLiipImagineBundle\Provider;

use Sonata\MediaBundle\Model\MediaInterface;

use Sonata\MediaBundle\Provider\MediaProviderInterface;

trait ProviderOverrideTrait
{
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
     * @param MediaInterface $media
     * @param $format
     */
    public function resolveImageBox(MediaInterface $media, $format)
    {
        if (MediaProviderInterface::FORMAT_REFERENCE === $format) {
            $box = $media->getBox();
        } else {
            $resizerFormat = $this->getFormat($format);
            $resizerFormat['format'] = $format;
            $resizerFormat['path'] = $this->generatePublicUrl($media, $format);

            if ($resizerFormat === false) {
                throw new \RuntimeException(sprintf('The image format "%s" is not defined.
                        Is the format registered in your ``Liip imagine`` configuration?', $format));
            }

            $box = $this->resizer->getBox($media, $resizerFormat);
        }

        return $box;
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

    /**
     * {@inheritdoc}
     */
    public function generatePublicUrl(MediaInterface $media, $format)
    {
        if (MediaProviderInterface::FORMAT_REFERENCE === $format) {
            $path = $this->getReferenceImage($media);
        } elseif (MediaProviderInterface::FORMAT_ADMIN === $format) {
            $path = sprintf('%s/thumb_%s_%s.%s', $this->generatePath($media), $media->getId(), $format, $media->getExtension());
        } else {
            return $this->thumbnail->generatePublicUrl($this, $media, $format);
        }

        return $this->getCdn()->getPath($path, $media->getCdnIsFlushable());
    }

    /**
     * {@inheritdoc}
     */
    public function generatePrivateUrl(MediaInterface $media, $format)
    {
        return $this->thumbnail->generatePrivateUrl($this, $media, $format);
    }
}
