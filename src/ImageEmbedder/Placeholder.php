<?php
namespace Tch\TwiggedSwiftMessageBuilder\ImageEmbedder;

class Placeholder
{
    private $placeholder;
    private $imagePath;

    public function __construct($placeholder, $imagePath)
    {
        $this->placeholder = $placeholder;
        $this->imagePath = $imagePath;
    }

    /**
     * @return string
     */
    public function getPlaceholder()
    {
        return $this->placeholder;
    }

    /**
     * @return string
     */
    public function getImagePath()
    {
        return $this->imagePath;
    }
}
