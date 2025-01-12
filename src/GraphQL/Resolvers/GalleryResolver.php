<?php

namespace App\GraphQL\Resolvers;

use App\Models\Gallery;

class GalleryResolver
{
    private $galleryModel;

    public function __construct()
    {
        $this->galleryModel = new Gallery();
    }

    public function resolveGallery($productId)
    {
        return $this->galleryModel->getByProductId($productId);
    }
}
