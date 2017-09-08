<?php

namespace AppBundle\Services;

use Firebase\JWT\JWT;

class JwtAuth{
    public $manager;
    public $signUp;
    public $key;
   
    public function __construct($manager){
        $this->manager = $manager;
        $this->signUp = false;
        $this->key = '+*234~·$6*+';
    }

    public function signUp($email,$password, $getHash = null){

        $user = $this->manager->getRepository('BackendBundle:User')->findOneBy(array(
            //sólo me devuelve un objeto user cuando el email y el pass coincidan en la bbdd
            //SELECT name From users WHERE email=$email && password=$password
            "email"=> $email,
            "password"=>$password
        ));

        //si devuelve un objeto mediante el where es que existe ese user
        if(is_object($user)){
            $this->signUp = true;
        }

        //si es true, el user coincide
        if($this->signUp){
            //Generar tokens
            $token = array(
                "sub" => $user->getId(),
                "email" => $user->getEmail(),
                "name" => $user->getName(),
                "surname" => $user->getSurname(),
                "iat" => time(),
                "exp" => time() + (7 * 24 * 60 * 60)
            );
            
            //encode token
            $jwt = JWT::encode($token,$this->key, 'HS256');
            //decode token
            $decoded = JWT::decode($jwt, $this->key, array('HS256'));

            //si el hash es null recibo el token codificado
            if($getHash == null){
                $data = $jwt;
            //si no es null lo decodifico
            }else{
                $data = $decoded;
            }

        }else{
            $data = array(
                'status' => 'error',
                'message' => "The user doesn't exist"
            );
        }

        
        return $data;
    }


    public function checkToken($jwt,$getIdentity = false){
        $auth = false;
        
        try{
            //Comprobamos si es el mismo token con la key
            $decoded = JWT::decode($jwt,$this->key,array('HS256'));
        }catch(\UnexpectedValueException $e){
            $auth = false;
        }catch(\DomainException $e){
            $auth = false;
        }

        //si decoded es un objeto correcto  user y existe el ide del user
        if(isset($decoded) && is_object($decoded) && isset($decoded->sub)){
            $auth = true;
        }else{
            $auth = false;
        }

        if($getIdentity == false){
            return $auth;
        }else{
            return $decoded;
        }


    }
}