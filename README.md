# Sonata Media integration with Liip Imagine

## Overview
Integrates [Sonata Media Bundle](https://sonata-project.org/bundles/media/3-x/doc/index.html) and [Liip Imagine Bundle](https://github.com/liip/LiipImagineBundle) without pain.

If you have tried to integrated LiipImagineBundle and Sonata Media Bundle as was described on [this page](https://sonata-project.org/bundles/media/3-x/doc/reference/extra.html#liip-imagine-bundle-integration)
you know what I is talking about.
I've decided to free the Symfony world from such a headache.

The bundle provides a support for LiipImagineBundle through a specific Thumbnail service like as original Sonata Media.
It also works good with Sonata Admin Bundle. So you no need any other actions for your admin part if you use Sonata Admin Bundle.
You feel free to try this. Configure bundle takes about couple minutes. 

### 1 Installation
`$ composer require enemis/sonata-media-liip-imagine`

### 2  Enable the Bundle
```php
<?php
// app/AppKernel.php

// ...
class AppKernel extends Kernel
{
    public function registerBundles()
    {
        $bundles = array(
            // ...

            new Enemis\SonataMediaLiipImagineBundle\SonataMediaLiipImagineBundle(),
        );

        // ...
    }

    // ...
}
```

### 3 Sonata Media config
    You need to configure your Sonata Media Bundle. Most important part is to configure context, cdn, providers.
    You mustn't add formats to contexts that should use Liip formats. Tell to providers to use custom thumbnail service
    'sonata.media.thumbnail.liip_imagine'. ** Please do not forget this bundle allow use Liip imagine only with image provider and youtube provider.
     If you require something else please let me know or suggest pull request **
     
```yaml
#config.yml
sonata_media:
    # if you don't use default namespace configuration
    #class:
    #    media: MyVendor\MediaBundle\Entity\Media
    #    gallery: MyVendor\MediaBundle\Entity\Gallery
    #    gallery_has_media: MyVendor\MediaBundle\Entity\GalleryHasMedia
    db_driver: doctrine_orm # or doctrine_mongodb, doctrine_phpcr it is mandatory to choose one here
    default_context: photo # you need to set a context
    force_disable_category: true #true, if you really want to disable the relation with category
    category_manager:  null #null or "sonata.media.manager.category.default" if classification bundle exists
    admin_format: { width: 500 , quality: 90, format: 'jpg'}
    contexts:
        photo:
             download:
                 strategy: sonata.media.security.superadmin_strategy
                 mode: http
             providers:
                - sonata.media.provider.image
             formats: ~
        video:
             download:
                 strategy: sonata.media.security.superadmin_strategy
                 mode: http
             providers:
                - sonata.media.provider.youtube
             formats: ~
    cdn:
        server:
            path: /uploads/media #define path where sonata will be store uploaded files
    filesystem:
        local:
            directory:  "%kernel.root_dir%/../web/"
            create:     false
    providers:
        image:
            allowed_extensions: ['jpg', 'png', 'jpeg']
            allowed_mime_types: ['image/pjpeg', 'image/jpeg', 'image/png', 'image/x-png']
            thumbnail:  sonata.media.thumbnail.liip_imagine #if you want use Liip with this provider you have to use this thumbnail service
        youtube:
            thumbnail:  sonata.media.thumbnail.liip_imagine
            html5: true
```    
More details you are able to find on [official documentation](https://sonata-project.org/bundles/media/3-x/doc/reference/advanced_configuration.html). 

### 4 Configure you Liip Imagine and add filters sets
You need to add filter sets in format CONTEXT_FORMAT. You are able to use any filters or post-processors that Liip Imagine provides. 
```yaml
#config.yml
liip_imagine:
    filter_sets:
        video_wide:
            filters:
                downscale:
                     max: [970, 500]
                watermark:
                     # path to the watermark file (prepended with "%kernel.root_dir%")
                     image: '../web/watermark.png'
                     # size of the water mark relative to the input image
                     size: 0.3
                     position: bottomright
            post_processors:
                mozjpeg: { quality: 80 }

        photo_wide:
            filters:
                downscale:
                     max: [970, 500]
                watermark:
                     # path to the watermark file (prepended with "%kernel.root_dir%")
                     image: '../web/watermark.png' 
                     # size of the water mark relative to the input image
                     size: 0.3
                     position: bottomright

        photo_preview:
            filters:
                downscale:
                     max: [250, 250]

        video_preview:
            filters:
                downscale:
                     max: [100, 250]
            post_processors:
                mozjpeg: { quality: 80 }
```
[More details](http://symfony.com/doc/master/bundles/LiipImagineBundle/index.html) about Liip Imagine

### 5 Override Sonata's Media provider.
In general, this bundle override sonata's providers(ImageProvider and YoutubeProvider). 
If you wanna override some provider's part, you have to extend own provider 
 with Enemis\SonataMediaLiipImagineBundle\Provider\ImageProvider.
or Enemis\SonataMediaLiipImagineBundle\Provider\YouTubeProvider for youtube provider and override the corresponding parameter.
Sonata Media has two parameters for that. Just override it if you want. 
* sonata.media.provider.image.class: Application\Sonata\MediaBundle\Provider\ImageProvider
* sonata.media.provider.youtube.class: Application\Sonata\MediaBundle\Provider\YouTubeProvider

You are able to put this parameters into parameters.yml, config.yml. I prefere add them in admin.yml from Application\Sonata\MediaBundle.

```yaml
#admin.yml
parameters:
    sonata.media.provider.image.class: Application\Sonata\MediaBundle\Provider\ImageProvider
    sonata.media.provider.youtube.class: Application\Sonata\MediaBundle\Provider\YouTubeProvider
```
and don't forget extend your provider by provider from Enemis\SonataMediaLiipImagineBundle\Provider namespace.
```php
<?php
#Application\Sonata\MediaBundle\Provider\YouTubeProvider

namespace Application\Sonata\MediaBundle\Provider;

use Enemis\SonataMediaLiipImagineBundle\Provider\YouTubeProvider as EnemisYoutubeProvider;

class YouTubeProvider extends EnemisYoutubeProvider
{
    /**
     * Get provider reference url.
     *
     * @param MediaInterface $media
     *
     * @return string
     */
    public function getReferenceUrl(MediaInterface $media)
    {
        return sprintf('https://www.youtube.com/watch?v=%s', $media->getProviderReference());
    }
}
```

If you have some question feel free to mail me on stadnikandreypublic@gmail.com. 
Also you are able to create issues and suggest pull request.
# Enjoy
![Alt text](https://pp.userapi.com/c840420/v840420290/29bfe/QZEhQLnvpbQ.jpg)