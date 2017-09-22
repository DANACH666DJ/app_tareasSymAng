<?php

namespace AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Constraints as Assert;
use BackendBundle\Entity\User;
use AppBundle\Services\Helpers;
use AppBundle\Services\JwtAuth;

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

               //cifrar la password
               $pwd = hash('sha256',$password);
               $user->setPassword($pwd);

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



    public function editAction(Request $request){
        //Para recibir el token via post
        $token = $request->get('authorization',null);
        //Para poder usar nuestro servicio jwAuth
        $jwt_auth =$this->get(JwtAuth::class);
        //Para poder usar nuestro servicio helpers
        $helpers = $this->get(Helpers::class);


        //comprobamos si el token es correcto
        $authChek = $jwt_auth ->checkToken($token);

        if($authChek){
            //entity manager
            $em = $this->getDoctrine()->getManager();

            //para conseguir los datos del token,es decir, del user identificado
            $identity = $jwt_auth ->checkToken($token,true);
            //SELECT name From users WHERE id=$user->sub
            //para conseguir el user a actualizar
            $user = $em->getRepository('BackendBundle:User')->findOneBy(array(
                "id" => $identity->sub
            ));

            //Recibir json por POST
            $json = $request->get('json',null);
        
            $data = array(
                'status' => 'error',
                'code' => 400,
                'message' => 'User not updated');
        
            if($json !=null){
                //Convertimos un json a un objeto php para poder acceder a su propiedades
                $params = json_decode($json);
        
                //$createdAt = new \Datetime("now");
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
                if($email != null && count($validate_email) == 0 && $name !=null && $surname !=null){
                    //$user->setCreatedAt($createdAt);
                    $user->setRole($role);
                    $user->setEmail($email);
                    $user->setName($name);
                    $user->setSurname($surname);

                    //if password is not null,save password encoding
                    if($password !=null){
                        //encode password
                        $pwd = hash('sha256',$password);
                        $user->setPassword($pwd);
                    }
                   

                    //necesario para guardar en bbdd o hacer selects
                    $em = $this->getDoctrine()->getManager();
                    //para comprobar si existe un user con el mismo email
                    //SELECT name From users WHERE email=$email
                    $isset_user = $em->getRepository('BackendBundle:User')->findBy(array(
                           "email" => $email
                       ));
        
                    //si no me saca ningun user,es decir me da 0,el usuario no existe y el email no esta registrado en la bbdd
                    if(count($isset_user) == 0 || $identity->email == $email){
                        //para persistir los datos en doctrine
                        $em->persist($user);
                        //para guardar los datos en mysql
                        $em->flush();
                           
        
                        $data = array(
                               'status' => 'succes',
                               'code' => 200,
                               'message' => 'User updated',
                               'user' => $user
                            );
                        }else{
                            $data = array(
                                'status' => 'error',
                                'code' => 400,
                                'message' => 'User not updated, duplicated'
                            );
                        }
                    }
                }

        }else{
            $data = array(
                'status' => 'error',
                'code' => 400,
                'message' => 'Authorization not valid'
            );
        }

        return $helpers->json($data);

     }


}
