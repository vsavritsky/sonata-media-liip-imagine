<?php

namespace Enemis\SonataMediaLiipImagineBundle\Provider;

use Sonata\MediaBundle\Model\MediaInterface;
use \Sonata\MediaBundle\Provider\YouTubeProvider as SonataYouTubeProvider;

class YouTubeProvider extends SonataYouTubeProvider
{
    use ProviderOverrideTrait;

    /**
     * @param MediaInterface $media
     * @param string         $format
     * @param array          $options
     *
     * @return Box
     */
    protected function getBoxHelperProperties(MediaInterface $media, $format, $options = array())
    {
        return $this->resolveImageBox($media, $format);
    }

    /**
     * {@inheritdoc}
     */
    public function getReferenceImage(MediaInterface $media)
    {
        return sprintf(
            '%s/%s.jpg',
            $this->generatePath($media),
            $media->getProviderReference()
        );
    }

    /**
     * {@inheritdoc}
     */
    public function postPersist(MediaInterface $media)
    {
        $this->generateReference($media);
        parent::postPersist($media);
    }

    /**
     * Set the file contents for an image.
     */
    protected function generateReference(MediaInterface $media)
    {
        if (!$media->getBinaryContent()) {
            return;
        }

        $file = $this->getFilesystem()->get(sprintf('%s/%s.jpg', $this->generatePath($media), $media->getProviderReference()), true);

        $content = file_get_contents($media->getProviderMetadata()['thumbnail_url']);

        if ($content) {
            $result = $file->setContent($content);

            return;
        }
    }
}
