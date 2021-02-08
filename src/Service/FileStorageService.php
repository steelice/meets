<?php


namespace App\Service;


use Symfony\Component\HttpFoundation\File\UploadedFile;

class FileStorageService
{
    private string $uploadPath;
    private ImageService $imageService;
    private string $uploadFolder;

    public function __construct(string $uploadDir, string $uploadFolder, ImageService $imageService)
    {
        $this->uploadPath = $uploadDir . DIRECTORY_SEPARATOR . $uploadFolder;
        $this->imageService = $imageService;
        $this->uploadFolder = $uploadFolder;
    }

    /**
     * Сохраняет загруженный файл
     *
     * @param UploadedFile $uploadedFile
     * @param string|null $destinationFilename
     * @return string
     */
    public function saveFile(UploadedFile $uploadedFile, ?string $destinationFilename = null): string
    {
        if (!$destinationFilename) {
            $destinationFilename = $this->getRandomFilename();
        }
        $destinationFilename .= '.' . $uploadedFile->guessExtension();
        $fullPath = $this->uploadPath . DIRECTORY_SEPARATOR . $this->getFileDir($destinationFilename);
        $fullName = $fullPath . DIRECTORY_SEPARATOR . $destinationFilename;

        $uploadedFile->move($fullPath, $destinationFilename);
        return $fullName;
    }

    /**
     * Сохраняет загруженный файл как картинку
     *
     * @param UploadedFile $uploadedFile
     * @param int $width
     * @param int $height
     * @param string|null $destinationFilename
     * @return string
     */
    public function saveImage(UploadedFile $uploadedFile, int $width, int $height, ?string $destinationFilename = null): string
    {
        $fullPath = $this->saveFile($uploadedFile, $destinationFilename);
        $this->imageService->resizeImage($fullPath, $width, $height);
        return $fullPath;
    }

    /**
     * Возвращает случаное имя файла
     *
     * @return string
     */
    public function getRandomFilename(): string
    {
        return uniqid('', true);
    }

    /**
     * Возвращает каталог для файла
     * Используется для распределения файлов по разным каталогам, чтобы не упереться в лимиты файловой системы при огромном количестве файлов
     *
     * @param string $filename
     * @return string
     */
    public function getFileDir(string $filename): string
    {
        return $filename[0] . DIRECTORY_SEPARATOR . $filename[1];
    }

    /**
     * Возвращает урл файла для получения к ним доступа из шаблонов
     *
     * @param string $filename
     * @return string
     */
    public function getFileUrl(string $filename): string
    {
        return '/' . implode('/', [
            $this->uploadFolder,
            $filename[0],
            $filename[1],
            $filename
        ]);
    }
}