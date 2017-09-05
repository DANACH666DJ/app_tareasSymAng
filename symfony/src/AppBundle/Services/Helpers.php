<?php

namespace AppBundle\Services;

class Helpers{
    public $manager;

    public function __construct($manager){
        $this->manager = $manager;
    }

    public function json($data){
        //A method that allows us to normalizer data
        $normalizers = array(new \Symfony\Component\Serializer\Normalizer\GetSetMethodNormalizer());
        //A method that allows us to encode json 
        $encoders = array("json" => new \Symfony\Component\Serializer\Encoder\JsonEncoder());

        //A method that allows us to serializer json 
        $serializer = new \Symfony\Component\Serializer\Serializer($normalizers,$encoders);
        $json = $serializer->serialize($data, 'json');
        

        //This is going to make us an http response
        $response = new \Symfony\Component\HttpFoundation\Response();
        $response->setContent($json);
        $response->headers->set('Content-Type','application/json');

        return $response;






    }

    
}