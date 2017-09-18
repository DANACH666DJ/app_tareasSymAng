<?php

namespace AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Constraints as Assert;
use BackendBundle\Entity\User;
use AppBundle\Services\Helpers;

class UserController extends Controller {

    public function newAction(Request $request){
       //Para poder usar nuestro servicio helpers
       $helpers = $this->get(Helpers::class);

       //Recibir json por POST
       $json = $request->get('json',null);

       $data = array(
        'status' => 'error',
        'code' => 400,
        'message' => 'User not created');

       if($json !=null){
           //Convertimos un json a un objeto php para poder acceder a su propiedades
           $params = json_decode($json);

           $createdAt = new \Datetime("now");
           $role = 'user';

           //si existe el email le das el valor de params->email si no, se pone a null
           $email = (isset($params->email)) ? $params->email :null;
           $name = (isset($params->name)) ? $params->name :null;
           $surname = (isset($params->surname)) ? $params->surname :null;
           $password = (isset($params->password)) ? $params->password :null;

           //Validate email
           $emailConstraint = new Assert\Email();
           //add message error
           $emailConstraint->message = "This email is not valid !!";
           $validate_email = $this->get("validator")->validate($email,$emailConstraint);

           //si es igual a 0 el email se valida correctamente
           if($email != null && count($validate_email) == 0 && $password !=null && $name !=null && $surname !=null){
               //creamos un object user para agregarle las propiedades mediante los setters
               $user = new User();
               $user->setCreatedAt($createdAt);
               $user->setRole($role);
               $user->setEmail($email);
               $user->setName($name);
               $user->setSurname($surname);
               //necesario para guardar en bbdd o hacer selects
               $em = $this->getDoctrine()->getManager();
               //para comprobar si existe un user con el mismo email
               //SELECT name From users WHERE email=$email
               $isset_user = $em->getRepository('BackendBundle:User')->findBy(array(
                   "email" => $email
               ));

               //si no me saca ningun user,es decir me da 0,el usuario no existe y el email no esta registrado en la bbdd
               if(count($isset_user) == 0){
                   //para persistir los datos en doctrine
                   $em->persist($user);
                   //para guardar los datos en mysql
                   $em->flush();

                   $data = array(
                       'status' => 'succes',
                       'code' => 200,
                       'message' => 'User created',
                       'user' => $user
                    );
                }else{
                    $data = array(
                        'status' => 'error',
                        'code' => 400,
                        'message' => 'User not created, duplicated'
                    );
                }
            }
        }
        
        return $helpers->json($data);


    }


}
