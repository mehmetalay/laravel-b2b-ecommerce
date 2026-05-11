<?php

namespace App\Services;

use Intervention\Image\ImageManagerStatic as Image;

class ImageService
{
    protected $paths;
    protected $sizes;
    protected $quality;
    protected $default;

    public function __construct()
    {
        ini_set('max_execution_time', '1600');
        ini_set('memory_limit', '-1');

        $this->paths = config('images.paths');
        $this->sizes = config('images.sizes');
        $this->quality = config('images.quality');
        $this->default = config('images.default');
    }

    public function product($imagePath, $imageName)
    {
        $imagePath = $imagePath . '/' . $imageName;

        $jpgName = $this->convertToJpgName($imageName);

        $largePath = public_path($this->paths['products']['large']);
        $smallPath = public_path($this->paths['products']['small']);

        create_directory([
            $largePath,
            $smallPath
        ]);

        $largeFilePath = $largePath . $jpgName;
        $smallFilePath = $smallPath . $jpgName;

        $sizeLarge = $this->sizes['large'];
        $sizeSmall = $this->sizes['small'];

        $image = Image::make(file_get_contents($imagePath));

        $image = $this->resizeWithAspectRatio($image, $sizeLarge['width'], $sizeLarge['height'])
            ->resizeCanvas($sizeLarge['width'], $sizeLarge['height'], 'center', false, '#ffffff');

        if ($image->mime() === 'image/png') {
            $this->convertPngToJpg($image, $image->width(), $image->height())
                ->save($largeFilePath);
        } else {
            $image->save($largeFilePath);
        }

        $this->resizeWithoutCrop($largeFilePath, $smallFilePath, $sizeSmall['width'], $sizeSmall['height'], $this->quality['medium']);

        return $jpgName;
    }

    public function nopic_image($file)
    {
        $name = $this->default['no_image'];

        $largePath = public_path($this->paths['products']['large']);
        $smallPath = public_path($this->paths['products']['small']);

        create_directory([
            $largePath,
            $smallPath
        ]);

        $image = Image::make($file);

        $largeWidth = $this->sizes['large']['width'];
        $largeHeight = $this->sizes['large']['height'];

        $image = $this->resizeWithAspectRatio($image, $largeWidth, $largeHeight);

        $this->convertPngToJpg($image, $largeWidth, $largeHeight)
            ->save($largePath . $name);

        $smallWidth = $this->sizes['small']['width'];
        $smallHeight = $this->sizes['small']['height'];

        $image = $this->resizeWithAspectRatio($image, $smallWidth, $smallHeight);

        $this->convertPngToJpg($image, $smallWidth, $smallHeight)
            ->save($smallPath . $name, $this->quality['medium']);

        return true;
    }

    public function category($file, $name)
    {
        $path = public_path("{$this->paths['categories']}");

        create_directory($path);

        $sizeCategory = $this->sizes['category'];

        $this->resizeWithCrop($file, $path . $name, $sizeCategory['width'], $sizeCategory['height'], $this->quality['high']);

        return true;
    }

    public function brand($file, $name)
    {
        $path = public_path("{$this->paths['brands']}");

        $width = $this->sizes['brand']['width'];
        $height = $this->sizes['brand']['height'];

        create_directory($path);

        $image = Image::make($file);

        $this->resizeWithAspectRatio($image, ($width / 1.03), ($height / 1.03))
            ->resizeCanvas($width, $height, 'center', false, 'rgba(0, 0, 0, 0)')
            ->save($path . $name);

        return true;
    }

    public function logo($file, $logoType, $upload = false)
    {
        $path = public_path($this->paths['images']);

        $name = $this->default[$logoType];
        $width = $this->sizes[$logoType]['width'];
        $height = $this->sizes[$logoType]['height'];

        create_directory($path);

        $image = Image::make($file);

        if ($width && $height) {
            $image = $this->resizeWithAspectRatio($image, $width, $height)
                ->resizeCanvas($width, $height, 'center', false, 'rgba(0, 0, 0, 0)');
        }

        $image->save($path . $name);

        if ($upload) {
            $this->productImageLazyLoad($file, 'lazy-load.jpg');

            $size = $this->sizes['image_og'];

            $this->convertPngToJpg($path . $name, $size['width'], $size['height'])
                ->save($path . $this->default['image_og']);
        }

        return $name;
    }

    public function favicon($file)
    {
        $path = public_path($this->paths['favicon']);
        create_directory($path);

        $names = $this->default['favicon'];
        $sizes = $this->sizes['favicon'];

        foreach ($names as $key => $name) {
            $width = $sizes[$key]['width'];
            $height = $sizes[$key]['height'];
            $bg = $sizes[$key]['background'] ?? 'transparent';

            $ext = strtolower(pathinfo($name, PATHINFO_EXTENSION));
            $savePath = $path . DIRECTORY_SEPARATOR . $name;

            $image = Image::make($file)
                ->fit($width, $height, function ($constraint) {
                    $constraint->upsize();
                });

            if ($bg !== 'transparent') {
                $canvas = Image::canvas($width, $height, $bg);
                $canvas->insert($image, 'center');
                $image = $canvas;
            }

            $image->encode($ext, 100)
                ->save($savePath);
        }

        return true;
    }

    public function productImageLazyLoad($file, $name)
    {
        $smallPath = public_path($this->paths['products']['small']);

        create_directory($smallPath);

        $smallWidth = $this->sizes['small']['width'];
        $smallHeight = $this->sizes['small']['height'];

        $image = Image::make($file);

        if ($image->height() < $image->width()) {
            $width = round($smallWidth / 1.2);
            $height = null;
        } else {
            $width = null;
            $height = round($smallHeight / 1.2);
        }

        $image = $this->resizeWithAspectRatio($image, $width, $height);

        $this->convertPngToJpg($image, $smallWidth, $smallHeight)
            ->save($smallPath . $name, $this->quality['low']);

        return true;
    }

    public function slider($file, $name, $sliderType)
    {
        $path = $this->paths[$sliderType]['desktop'];
        $width = $this->sizes[$sliderType]['width'];
        $height = $this->sizes[$sliderType]['height'];

        $sliderPath = public_path($path);

        create_directory($sliderPath);

        $pathinfo = strtolower(pathinfo($name, PATHINFO_EXTENSION));

        if ($pathinfo === 'gif') {
            copy($file->getRealPath(), $sliderPath . $name);
        } else {
            $this->resizeWithCrop($file, $sliderPath . $name, $width, $height, $this->quality['high']);
        }

        return $name;
    }

    public function sliderV2($file, string $name, string $device, string $type)
    {
        $paths = $this->paths;
        $sizes = $this->sizes;

        if (!isset($paths[$type][$device])) {
            throw new \InvalidArgumentException("Invalid slider path type: {$type} / {$device}");
        }

        if (!isset($sizes[$type])) {
            throw new \InvalidArgumentException("Invalid slider size type: {$type}");
        }

        $path = $paths[$type][$device];
        $width = $sizes[$type][$device]['width'] ?? null;
        $height = $sizes[$type][$device]['height'] ?? null;

        $sliderPath = public_path($path);
        create_directory($sliderPath);

        $extension = strtolower($file->getClientOriginalExtension());
        $fullPath = $sliderPath . $name;

        if ($extension === 'gif') {
            copy($file->getRealPath(), $fullPath);
        } else {
            if ($width && $height) {
                $this->smartResizeWithCrop(
                    $file,
                    $fullPath,
                    $width,
                    $height,
                    $this->quality['high']
                );
            } else {
                copy($file->getRealPath(), $fullPath);
            }
        }

        return $name;
    }

    public function product2($imagePath, $imageName)
    {
        $largePath = public_path($this->paths['products']['large']);
        $smallPath = public_path($this->paths['products']['small']);

        create_directory([
            $largePath,
            $smallPath
        ]);

        $jpgName = $this->convertToJpgName($imageName);

        $largeFilePath = $largePath . $jpgName;
        $smallFilePath = $smallPath . $jpgName;

        $sizeLarge = $this->sizes['large'];
        $sizeSmall = $this->sizes['small'];

        $image = Image::make($imagePath);

        $image = $this->resizeWithAspectRatio($image, $sizeLarge['width'], $sizeLarge['height'])
            ->resizeCanvas($sizeLarge['width'], $sizeLarge['height'], 'center', false, '#ffffff');

        if ($image->mime() === 'image/png') {
            $this->convertPngToJpg($image, $image->width(), $image->height())
                ->save($largeFilePath);
        } else {
            $image->save($largeFilePath);
        }

        $this->resizeWithoutCrop($largeFilePath, $smallFilePath, $sizeSmall['width'], $sizeSmall['height'], $this->quality['medium']);

        return $jpgName;
    }

    private function resizeWithoutCrop($sourcePath, $destinationPath, $width, $height, $quality)
    {
        $image = Image::make($sourcePath);

        $image = $this->resizeWithAspectRatio($image, $width, $height)
            ->resizeCanvas($width, $height, 'center', false, '#ffffff')
            ->save($destinationPath, $quality);
    }

    function resizeWithCrop($sourcePath, $destinationPath, $width, $height, $quality)
    {
        Image::make($sourcePath)
            ->fit($width, $height, function ($constraint) {
                $constraint->upsize();
            }, 'center')
            ->save($destinationPath, $quality);
    }

    function smartResizeWithCrop($sourcePath, $destinationPath, $width, $height, $quality)
    {
        $image = Image::make($sourcePath);

        $shouldUpsize =
            $image->width() < $width ||
            $image->height() < $height;

        $image->fit(
            $width,
            $height,
            $shouldUpsize ? null : function ($constraint) {
                $constraint->upsize();
            },
            'center'
        )->save($destinationPath, $quality);
    }

    private function convertToJpgName($imageName)
    {
        return pathinfo($imageName, PATHINFO_FILENAME) . '.jpg';
    }

    private function convertPngToJpg($image, $width, $height)
    {
        return Image::canvas($width, $height, '#ffffff')
            ->insert($image, 'center')
            ->encode('jpg', $this->quality['high']);
    }

    private function resizeWithAspectRatio($image, $width, $height)
    {
        return $image->resize($width, $height, function ($constraint) {
            $constraint->aspectRatio();
        });
    }
}
