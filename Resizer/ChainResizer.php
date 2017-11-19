<?php

namespace Enemis\SonataMediaLiipImagineBundle\Resizer;

use Doctrine\Common\Collections\ArrayCollection;
use Gaufrette\File;
use Imagine\Exception\InvalidArgumentException;
use Imagine\Image\Box;
use Imagine\Image\ImageInterface;
use Imagine\Image\ImagineInterface;
use Sonata\MediaBundle\Metadata\MetadataBuilderInterface;
use Sonata\MediaBundle\Model\MediaInterface;
use Sonata\MediaBundle\Resizer\ResizerInterface;

class ChainResizer implements ResizerInterface
{
    /**
     * @var ImagineInterface
     */
    protected $adapter;

    /**
     * @var string
     */
    protected $mode;

    /**
     * @var MetadataBuilderInterface
     */
    protected $metadata;

    /**
     * @var ResizerInterface
     */
    protected $liipResizer;

    /**
     * @var ResizerInterface
     */
    protected $sonataResizer;

    /**
     * @var array
     */
    protected $filterSets;

    /**
     * @param ResizerInterface $resizer
     */
    public function setLiipResizer(ResizerInterface $resizer)
    {
        $this->liipResizer = $resizer;
    }

    /**
     * @param ResizerInterface $resizer
     */
    public function setSonataResizer(ResizerInterface $resizer)
    {
        $this->sonataResizer = $resizer;
    }

    /**
     * @param array $formats
     */
    public function setFilterSets(array $formats)
    {
        $this->filterSets = $formats;
    }

    /**
     * @param ImagineInterface         $adapter
     * @param string                   $mode
     * @param MetadataBuilderInterface $metadata
     */
    public function __construct(ImagineInterface $adapter, $mode, MetadataBuilderInterface $metadata)
    {
        $this->adapter = $adapter;
        $this->mode = $mode;
        $this->metadata = $metadata;
    }

    /**
     * {@inheritdoc}
     */
    public function resize(MediaInterface $media, File $in, File $out, $format, array $settings)
    {
        return $this->sonataResizer->resize($media, $in, $out, $format, $settings);
    }

    /**
     * {@inheritdoc}
     */
    public function getBox(MediaInterface $media, array $settings)
    {
        if (array_key_exists($settings['format'], $this->filterSets)) {
            return $this->liipResizer->getBox($media, $settings);
        } else {
            return $this->sonataResizer->getBox($media, $settings);
        }
    }

    /**
     * @throws InvalidArgumentException
     *
     * @param MediaInterface $media
     * @param array          $settings
     *
     * @return Box
     */
    protected function computeBox(MediaInterface $media, array $settings)
    {
        return $this->computeBox($media, $settings);
    }
}
