<?php

namespace Enemis\SonataMediaLiipImagineBundle\Resizer;

use Gaufrette\File;
use Imagine\Exception\InvalidArgumentException;
use Imagine\Image\Box;
use Imagine\Image\ImageInterface;
use Imagine\Image\ImagineInterface;
use Sonata\MediaBundle\Metadata\MetadataBuilderInterface;
use Sonata\MediaBundle\Model\MediaInterface;
use Sonata\MediaBundle\Resizer\ResizerInterface;
use Symfony\Bundle\FrameworkBundle\Routing\Router;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\HttpKernel;
use Symfony\Component\HttpKernel\HttpKernelInterface;

class LiipResizer implements ResizerInterface
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
     * @var HttpKernel
     */
    protected $httpKernel;

    /**
     * @var Router
     */
    protected $router;

    /**
     * @var MetadataBuilderInterface
     */
    protected $metadata;

    /**
     * @var string
     */
    protected $rootDir;

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
     * @param string $rootDir
     */
    public function setRootDir(string $rootDir)
    {
        $this->rootDir = $rootDir;
    }

    /**
     * @param Router $router
     */
    public function setRouter(Router $router)
    {
        $this->router = $router;
    }

    /**
     * @param HttpKernel $httpKernel
     */
    public function setHttpKernel(HttpKernel $httpKernel)
    {
        $this->httpKernel = $httpKernel;
    }

    /**
     * {@inheritdoc}
     */
    public function resize(MediaInterface $media, File $in, File $out, $format, array $settings)
    {
        throw new \Exception('You mustn\'t call this method.');
    }

    /**
     * {@inheritdoc}
     */
    public function getBox(MediaInterface $media, array $settings)
    {
        $filePath = parse_url($settings['path'])['path'];
        $filePath = $this->rootDir . '/../web' . $filePath;

        if (!file_exists($filePath)) {
            $relativePath = parse_url($settings['path'])['path'];
            $matched = $this->router->match($relativePath);
            $subRequest = new Request();
            $subRequest->attributes->set('_controller', $matched['_controller']);
            $subRequest->attributes->set('filter', $matched['filter']);
            $subRequest->attributes->set('_route', $matched['_route']);
            if (isset($matched['_locale'])) {
                $subRequest->attributes->set('_locale', $matched['_locale']);
            }
            $subRequest->attributes->set('path', $matched['path']);

            $response = $this->httpKernel->handle($subRequest, HttpKernelInterface::SUB_REQUEST);

            $filePath = str_replace('resolve/', '', $filePath);
            if (isset($matched['_locale'])) {
                $filePath = str_replace(sprintf('/%s/', $matched['_locale']), '/', $filePath);
            }
        }
        $image = $this->adapter->open($filePath);

        return $image->getSize();
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
        throw new \Exception('You mustn\'t call this method.');
    }
}
