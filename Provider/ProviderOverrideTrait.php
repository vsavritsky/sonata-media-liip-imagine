<?php

namespace Enemis\SonataMediaLiipImagineBundle\Provider;

use Sonata\MediaBundle\Model\MediaInterface;

use Sonata\MediaBundle\Provider\MediaProviderInterface;

trait ProviderOverrideTrait
{
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
    public function generatePublicUrl(MediaInterface $media, $format)
    {
        if (MediaProviderInterface::FORMAT_REFERENCE === $format) {
            $path = $this->getReferenceImage($media);
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

    public function flushCdn(MediaInterface $media)
    {
        if ($media->getId()) {
            $formatsBeDeleted = [];
            foreach ($this->getFormats() as $format => $settings) {
                if (substr($format, 0, strlen($media->getContext())) === $media->getContext()) {
                    $formatsBeDeleted[] = $format;
                }
            }

            if (!empty($formatsBeDeleted)) {
                $this->thumbnail->delete($this, $media, $formatsBeDeleted);
            }
        }
    }
}
