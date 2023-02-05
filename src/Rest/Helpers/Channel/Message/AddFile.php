<?php

namespace Exan\Dhp\Rest\Helpers\Channel\Message;

trait AddFile
{
    /**
     * @var string $contentType
     *  Content-Type header to be used for this file.
     *  If not provided, this is guessed based on file extension.
     * @see https://discord.com/developers/docs/reference#uploading-files
     */
    public function addFile(string $fileName, string $content, ?string $contentType = null): self
    {
        $file = [
            'name' => $fileName,
            'content' => $content,
        ];

        $this->files[] = &$file;

        if (!is_null($contentType)) {
            $file['type'] = $contentType;

            return $this;
        }

        $fileInfo = pathinfo($fileName);
        if (empty($fileInfo['extension'])) {
            return $this;
        }

        $type = (new \Mimey\MimeTypes())->getMimeType($fileInfo['extension']);

        if (!is_null($type)) {
            $file['type'] = $type;
        }

        return $this;
    }
}