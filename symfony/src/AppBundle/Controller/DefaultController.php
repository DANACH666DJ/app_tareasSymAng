<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Validator\Constraints as Assert;
use AppBundle\Services\Helpers;
use AppBundle\Services\JwtAuth;

class DefaultController extends Controller
{
    
    public function indexAction(Request $request)
    {
        // replace this example code with whatever you need
        return $this->render('default/index.html.twig', [
            'base_dir' => realpath($this->getParameter('kernel.project_dir')).DIRECTORY_SEPARATOR,
        ]);
    }


    //estos métodos necesitan Action para poder ser accesibles como ruta en la app
    public function loginAction(Request $request){
       //Para poder usar nuestro servicio helpers
       $helpers = $this->get(Helpers::class);

       //Recibir json por POST
       $json = $request->get('json',null);


       $data = array(
           'status' => 'error',
           'data' => 'Not data'
        );


       if($json !=null){
           //Convertimos un json a un objeto php
           $params = json_decode($json);

           //si existe el email le das el valor de params->email si no, se pone a null
           $email = (isset($params->email)) ? $params->email :null;
           $password = (isset($params->password)) ? $params->password :null;
           $getHash = (isset($params->getHash)) ? $params->getHash :null;

           //Validate email
           $emailConstraint = new Assert\Email();
           //add message error
           $emailConstraint->message = "This email is not valid !!";
           $validate_email = $this->get("validator")->validate($email,$emailConstraint);


           //si es igual a 0 el email se valida correctamente
           if($email != null && count($validate_email) == 0 && $password !=null){
               //encode password
               $pwd = hash('sha256',$password);
               //echo var_dump($pwd);

               $jwt_auth =$this->get(JwtAuth::class);

               if($getHash == null || $getHash == false){
                   $signUp = $jwt_auth->signUp($email,$pwd);
               }else{
                   $signUp = $jwt_auth->signUp($email,$pwd, true);
               }

               return $this->json($signUp);

            }else{
                $data = array(
                    'status' => 'error',
                    'data' => 'Email or password incorrect'
                );
            }
        }

       return $helpers->json($data);

    }


    public function pruebasAction(Request $request){
        $token = $request->get('authorization',null);
        $helpers = $this->get(Helpers::class);
        $jwt_auth =$this->get(JwtAuth::class);

        //si nos llega el token y checkToken devuelve true
        if($token && $jwt_auth ->checkToken($token)){
            $em = $this->getDoctrine()->getManager();
            $userRepo = $em->getRepository('BackendBundle:User');
            $users = $userRepo->findAll();
    
            return $helpers->json(array(
                'status' => 'succes',
                'users' => $users
            ));
        }else{
            return $helpers->json(array(
                'status' => 'error',
                'code' => 400,
                'message' => 'Authorization not valid'
            ));

        }

        
    }
}
