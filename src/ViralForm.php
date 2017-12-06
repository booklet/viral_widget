<?php
class ViralForm
{
    public function get($resource)
    {
        $api = new self;
        return $api->request('GET', $resource);
    }

}
