<?php 

namespace Websystems\BoilrCore\Resources;

use Exception;

class PostType
{
    private $postType;
    private $args = [];

    public function __construct(string $postType, array $args = [])
    {
        $this->postType = $postType;
        $this->args = $args;
    }

    public function publish()
    {
        $postType = \register_post_type($this->postType, $this->args);
        if($postType instanceof \WP_Error) {
            throw New Exception($postType->get_error_message());
        }

        return $postType;
    }

    /**
     * Get the value of postType
     */ 
    public function getPostType()
    {
        return $this->postType;
    }

    /**
     * Get the value of args
     */ 
    public function getArgs()
    {
        return $this->args;
    }
}