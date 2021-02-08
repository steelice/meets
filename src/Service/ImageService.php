<?php


namespace App\Service;


use Imagine\Gd\Image;
use Imagine\Gd\Imagine;
use Imagine\Image\Box;

/**
 * Работа с изображениями. Для совместимости используется GD, может быть заменен на работу с Imagick
 */
class ImageService
{
    /**
     * Изменяет размер изображения с сохранением пропорций
     *
     * @param string $image
     * @param int $maxWidth
     * @param int $maxHeight
     * @param string|null $destination Если не указать, изображение сохранится поверх
     */
    public function resizeImage(string $image, int $maxWidth, int $maxHeight, ?string $destination = null): void
    {
        list($iWidth, $iHeight) = getimagesize($image);
        $ratio = $iWidth / $iHeight;
        if ($maxWidth / $maxHeight > $ratio) {
            $maxWidth = $maxHeight * $ratio;
        } else {
            $maxHeight = $maxWidth / $ratio;
        }

        $imagine = new Imagine();
        $source = $imagine->open($image);
        if (!$destination) {
            $destination = $image;
        }

        $source->resize(new Box($maxWidth, $maxHeight))->save($destination);
    }
}