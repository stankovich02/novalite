<?php

namespace NovaLite\Http;

class UploadedFile
{
    private string $extension;
    private string $name;
    private string $tmpName;
    private string $type;
    private int $size;
    private int $error;

    public function name(): string
    {
        return $this->name;
    }
    public function path()
    {
        return $this->tmpName;
    }

    public function extension(): string
    {
        return $this->extension;
    }

    public function tmpName(): string
    {
        return $this->tmpName;
    }

    public function type(): string
    {
        return $this->type;
    }

    public function size(): int
    {
        return $this->size;
    }

    public function error(): int
    {
        return $this->error;
    }

    public function __construct($file)
    {
        $this->extension = pathinfo($file['name'], PATHINFO_EXTENSION);
        $this->name = $file['name'];
        $this->tmpName = $file['tmp_name'];
        $this->type = $file['type'];
        $this->size = $file['size'];
        $this->error = $file['error'];
    }
}