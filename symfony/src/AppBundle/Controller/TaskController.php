<?php

namespace AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Constraints as Assert;
use BackendBundle\Entity\Task;
use AppBundle\Services\Helpers;
use AppBundle\Services\JwtAuth;

class TaskController extends Controller {

    public function newAction(Request $request){
        //Para recibir el token via post
        $token = $request->get('authorization',null);
        //Para poder usar nuestro servicio jwAuth
        $jwt_auth =$this->get(JwtAuth::class);
        //Para poder usar nuestro servicio helpers
        $helpers = $this->get(Helpers::class);


        //comprobamos si el token es correcto
        $authChek = $jwt_auth ->checkToken($token);

        echo var_dump($authChek);


        if($authChek){
             //para conseguir los datos del token,es decir, del user identificado
             $identity = $jwt_auth ->checkToken($token,true);

             //Recibir json por POST
             $json = $request->get('json',null);
       
            $data = array(
                'status' => 'error',
                'code' => 400,
                'message' => 'Not params');
       
            if($json !=null){
                 //Convertimos un json a un objeto php para poder acceder a su propiedades
                 $params = json_decode($json);

                 $createdAt = new \Datetime("now");
                 $updatedAt = new \Datetime("now");

                 $user_id = ( $identity->sub !=null) ? $identity->sub :null;
                 $title = (isset($params->title)) ? $params->title :null;
                 $description = (isset($params->description)) ? $params->description :null;
                 $status = (isset($params->status)) ? $params->status :null;

                 if( $user_id !=null &&  $title!=null){
                     $em = $this->getDoctrine()->getManager();
                     $user = $em->getRepository('BackendBundle:User')->findOneBy(array(
                         "id" => $user_id
                        ));

                    //create task object
                    $task = new Task();
                    $task ->setCreatedAt($createdAt);
                    $task ->setUpdatedAt($updatedAt);
                    $task ->setUser($user);
                    $task ->setTitle($title);
                    $task ->setDescription($description);
                    $task ->setStatus($status);

                    //para persistir los datos en doctrine
                    $em->persist($task);
                    //para guardar los datos en mysql
                    $em->flush();

                    $data = array(
                        'status' => 'succes',
                        'code' => 200,
                        'message' => 'Task created',
                        'data' => $task);
                }else{
                    $data = array(
                        'status' => 'succes',
                        'code' => 200,
                        'message' => 'Task not created, validation failed'
                    );
                }

            }else{
                $data = array(
                    'status' => 'error',
                    'code' => 400,
                    'message' => 'Task not created, params failed');
            }
        }else{
            $data = array(
                'status' => 'error',
                'code' => 400,
                'message' => 'Authorization failed');

        }

        return $helpers->json($data);
       
    }

   

}