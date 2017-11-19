<?php

namespace Enemis\SonataMediaLiipImagineBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class DefaultController extends Controller
{
    public function indexAction()
    {
        return $this->render('EnemisSonataMediaLiipImagineBundle:Default:index.html.twig');
    }
}
