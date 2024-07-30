<?php

namespace App\Service;

class UtilService
{

    public function resizeImage($filepath, $maxWidth = 800, $maxHeight = 800)
    {
        list($width, $height) = getimagesize($filepath);
        $ratio = $width / $height;
        if ($width > $maxWidth || $height > $maxHeight) {
            if ($maxWidth / $maxHeight > $ratio) {
                $maxWidth = $maxHeight * $ratio;
            } else {
                $maxHeight = $maxWidth / $ratio;
            }
            $newImage = imagecreatetruecolor($maxWidth, $maxHeight);
            $ext = pathinfo($filepath, PATHINFO_EXTENSION);
            if ($ext == 'jpg' || $ext == 'jpeg') {
                $image = imagecreatefromjpeg($filepath);
            } elseif ($ext == 'png') {
                $image = imagecreatefrompng($filepath);
            } elseif ($ext == 'gif') {
                $image = imagecreatefromgif($filepath);
            }
            imagecopyresampled($newImage, $image, 0, 0, 0, 0, $maxWidth, $maxHeight, $width, $height);
            imagejpeg($newImage, $filepath, 90);
        }
    }
}
