<?php

namespace Enemis\SonataMediaLiipImagineBundle\Provider;

use Gaufrette\Exception\FileNotFound;
use Sonata\MediaBundle\Model\MediaInterface;
use \Sonata\MediaBundle\Provider\YouTubeProvider as SonataYouTubeProvider;

class YouTubeProvider extends SonataYouTubeProvider
{
    use ProviderOverrideTrait;

    const GOOGLE_API_URL = 'https://www.googleapis.com/youtube/v3/';

    const FETCH_PARTS = [
        'statistics',
        'snippet',
        'localizations',
    ];

    const FETCH_FIELDS = 'items(etag,fileDetails,id,localizations,player(embedHeight,embedWidth),snippet(defaultLanguage,description,publishedAt,thumbnails(high,maxres,standard),title),statistics(dislikeCount,likeCount,viewCount),status,suggestions,topicDetails)';

    protected $apiKey;

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
     * @param mixed $apiKey
     */
    public function setApiKey(string $apiKey): void
    {
        $this->apiKey = $apiKey;
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

        try {
            $file->delete();
        }
        catch (FileNotFound $e) {
        }
        $file = $this->getFilesystem()->get(sprintf('%s/%s.jpg', $this->generatePath($media), $media->getProviderReference()), true);

        $content = file_get_contents($media->getProviderMetadata()['thumbnail_url']);

        if ($content) {
            $result = $file->setContent($content);

            return;
        }
    }

    public function updateMetadata(MediaInterface $media, $force = false)
    {
        $url = $this->buildUrl($media);

        try {
            $metadata = $this->getMetadata($media, $url);
        } catch (\RuntimeException $e) {
            $media->setEnabled(false);
            $media->setProviderStatus(MediaInterface::STATUS_ERROR);

            return;
        }

        $metadata = $this->adaptMetadata($metadata);

        $media->setProviderMetadata($metadata);

        if ($force) {
            $media->setDescription('description');
            $media->setName($localization['title']);
        }

        $this->generateReference($media);

        $media->setHeight(270);
        $media->setWidth(480);
        $media->setContentType('video/x-flv');
    }

    protected function buildUrl(MediaInterface $media): string {
        return \sprintf(
            '%svideos?part=%s&fields=%s&id=%s&key=%s',
            self::GOOGLE_API_URL,
            \implode(',', self::FETCH_PARTS),
            self::FETCH_FIELDS,
            $media->getProviderReference($media),
            $this->apiKey
        );
    }

    protected function adaptMetadata($metadata) {
        $metadata = $metadata['items'][0];
        $metadata = \array_merge($metadata, $metadata['snippet']);

        unset($metadata['snippet']);

        $biggestThumbnail = $this->lookupTheBiggestThumbnail($metadata);

        foreach ($biggestThumbnail as  $key => $value) {
            $metadata['thumbnail_' . $key] = $value;
        }

        return $metadata;
    }

    protected function lookupTheBiggestThumbnail($metadata) {
        \usort($metadata['thumbnails'], function ($a, $b) {
            return $b['width'] - $a['width'];
        });

        return $metadata['thumbnails'][0];
    }

}
