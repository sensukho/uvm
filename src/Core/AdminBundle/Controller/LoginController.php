<?php

namespace Core\AdminBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\Session\Session;

use Core\AdminBundle\Entity\Users;
use Core\AdminBundle\Entity\Radcheck;
use Core\AdminBundle\Entity\Ssidmacauth;

class LoginController extends Controller
{
########## LOGIN ##########
    /***************************************************************************/
    public function startAction(Request $request)
    {
	$params = '?';
        if( $request->query->all() ){
            foreach ($request->query->all() as $key => $value) {
                if ( $key == 'sip' || $key == 'client_mac' || $key == 'ssid' || $key == 'auth' ) {
                    $params .= $key.'='.$value.'&';
                }
            }
        }
        return $this->redirect( "/web/login/".$params );
    }
    /***************************************************************************/
    public function loginAction(Request $request)
    {
        $sesssion = $this->getRequest()->getSession();

        $sesssion->get('session_id');

        $ssid = NULL;
        $mac = NULL;
        $sip = NULL;
        $user_data = NULL;

        if( $request->query->all() ){
            $params = '?';
            foreach ($request->query->all() as $key => $value) {
                if ( $key == 'sip' || $key == 'client_mac' || $key == 'ssid' || $key == 'auth' ) {
                    $params .= $key.'='.$value.'&';
                }
            }
            $url = explode('&', $params);
        }else{
            $params = $request->request->get('params',NULL);
            $url = explode('&', $params);
        }

        foreach ($url as $value) {
            $pos = strpos($value, "auth");
            if ($pos === false) {  }
            else {
                $auth = explode('=', $value);
            }
        }

        foreach ($url as $value) {
            $pos = strpos($value, "sip");
            if ($pos === false) {  }
            else {
                $sip = explode('=', $value);
            }
        }

        if ( isset($auth) && $auth[1] == "failed" ) {
            return $this->redirect( "/web/login/error/" );
        }

        $request = Request::createFromGlobals();
        $user = $request->request->get('username',NULL);
        $pass = $request->request->get('password',NULL);
        $chk_rec = $request->request->get('chk_rec',NULL);
        $chk = '';
        $msg = '';

        /***** VERIFICA COOKIE *****/
        if ($account = $this->getcookie($sesssion)) {
            $url = "http://".$sip[1].":9997/login";
            //$url = "/web/bienvenida.html/";
            return $this->render('CoreAdminBundle:login:layout_zd.html.twig', array( 'user' => $account['user'], 'pass' => $account['pass'], 'url' => $url ));
        }

        if ($user != NULL && $pass != NULL) {

            $em = $this->getDoctrine()->getManager();

            /***** VERIFICA USUARIO *****/
            $raduser1 = $em->getRepository('CoreAdminBundle:Radcheck')->findOneBy(
                array(
                    'username'  => $user,
                    'value'  => $pass
                )
            );

            if (count($raduser1) < 1) {
                $msg = 'Usuario y/o contraseña inválidos';
                return $this->render('CoreAdminBundle:login:plantilla.html.twig', array( 'user' => $user, 'pass' => $pass, 'chk' => $chk, 'msg' => $msg, 'params' => $params ));
            }
            /***** VERIFICA SSID *****/
            foreach ($url as $value) {
                $pos = strpos($value, "ssid");
                if ($pos === false) {  }
                else {
                    $ssid = explode('=', $value);
                }
            }
            $raduser2 = $em->getRepository('CoreAdminBundle:Users')->findOneBy(
                array(
                    'username'  => $user,
                    'ssid'  => urldecode( trim($ssid[1]) )
                )
            );
            if (count($raduser2) < 1) {
                $user_data = $em->getRepository('CoreAdminBundle:Users')->findOneBy(
                    array(
                        'username'  => $user
                    )
                );
                $msg = 'A este usuario le corresponde la red "'.$user_data->getSsid().'" ( Conectado a: "'.urldecode( trim($ssid[1]) ).'" ). Por favor cambie de red WiFi e intente de nuevo.';
                return $this->render('CoreAdminBundle:login:plantilla.html.twig', array( 'user' => $user, 'pass' => $pass, 'chk' => $chk, 'msg' => $msg, 'params' => $params ));
            }

            /***** VERIFICA MACADDRESS *****/
            foreach ($url as $value) {
                $pos = strpos($value, "client_mac");
                if ($pos === false) {  }
                else {
                    $mac = explode('=', $value);
                }
            }
            $raduser3 = $em->getRepository('CoreAdminBundle:Ssidmacauth')->findOneBy(
                array(
                    'macaddress'  => $this->formatMac( $mac[1] )
                )
            );

            $exist_mac = 0;

            if (count($raduser3)) {
                if ($raduser3->getUsername() == $user ) {
                    $exist_mac = 1;
                }else{
                    $exist_mac = 2;
                    $msg = 'Ese dispositivo ya está registrado en otra cuenta. No puede registrarse el mismo dispositivo en cuentas diferentes. Favor de utilizar las credenciales de la primer cuenta con la que se conectó el dispositivo.';
                }
            }else{
                $user_mac = $em->getRepository('CoreAdminBundle:ssidmacauth')->findBy(
                    array(
                        'username'  => $user,
                    )
                );
                if (count($user_mac) > 1) {
                    $exist_mac = 3;
                    $msg = 'Ya existen dos dispositivos registrados con esta cuenta, lo cual es el máximo permitido. Por favor espera a mañana para poder registrar este dispositivo';
                }
            }

            /***** OTORGA ACCESO *****/
            if ($exist_mac == 0 || $exist_mac == 1) {
                /***** SETEA COOKIE *****/
                $this->setcookie($user, $pass, $raduser2->getCampus(), $chk_rec);
                /***** SETEA SESSION *****/
                $sesssion->set('user_campus', $raduser2->getCampus());
                $url = "http://".$sip[1].":9997/login";
                //$url = "/web/bienvenida.html/";
                return $this->render('CoreAdminBundle:login:layout_zd.html.twig', array( 'user' => $user, 'pass' => $pass, 'url' => $url ));
            }

            return $this->render('CoreAdminBundle:login:plantilla.html.twig', array( 'user' => $user, 'pass' => $pass, 'chk' => $chk, 'msg' => $msg, 'params' => $params ));

        }
        return $this->render('CoreAdminBundle:login:plantilla.html.twig', array( 'user' => $user, 'pass' => $pass, 'chk' => $chk, 'msg' => $msg, 'params' => $params ));
    }
    /***************************************************************************/
    public function errorAction()
    {
        return $this->render('CoreAdminBundle:login:error.html.twig', array());
    }
    /***************************************************************************/
    public function welcomeAction()
    {
        return $this->render('CoreAdminBundle:login:bienvenida.html.twig', array());
    }
    /***************************************************************************/
    public function registerAction(Request $request)
    {
        $usuario = new Users();
        $msg = '';

        if( $request->query->all() ){
            $params = '?';
            foreach ($request->query->all() as $key => $value){
                $params .= $key.'='.$value.'&';
            }
        }else{
            $form = $request->request->get('form',NULL);
            $params = $form['ssid'];
        }

        $usuario->setFecha( new \DateTime('today') );
        $form = $this->createFormBuilder($usuario)
            ->setAction($this->generateUrl('portal_register'))
                ->add('matricula', 'text', array('label' => 'Matrícula: ','attr' => array()))
                ->add('email', 'email', array('label' => 'E-mail: ','attr' => array()))
                ->add('genpass', 'email', array('label' => 'Confirmar: ','attr' => array()))
                ->add('username', 'text', array('label' => 'Usuario: ','attr' => array('pattern' => '.{5,}')))
                ->add('newpass', 'password', array('label' => 'Password: ','attr' => array('pattern' => '.{6,}')))
                ->add('newpasssecond', 'password', array('label' => 'Confirmar:','attr' => array('pattern' => '.{6,}')))
                ->add('ssid', 'hidden', array('attr' => array('value' => $params)))
                ->add('enviar', 'submit', array('attr' => array('class' => 'button blue sub-btn')))
        ->getForm();

        if ($request->isMethod('POST')) {
            $data = $request->request->all();

            $form = $this->createFormBuilder($usuario)
                ->setAction($this->generateUrl('portal_register'))
                ->add('matricula', 'text', array('label' => 'Matrícula: ','attr' => array('value' => $data['form']['matricula'])))
                ->add('email', 'email', array('label' => 'E-mail: ','attr' => array('value' => $data['form']['email'])))
                ->add('genpass', 'email', array('label' => 'Confirmar: ','attr' => array('value' => $data['form']['genpass'])))
                ->add('username', 'text', array('label' => 'Usuario: ','attr' => array('pattern' => '.{5,}','value' => $data['form']['username'])))
                ->add('newpass', 'password', array('label' => 'Password: ','attr' => array('pattern' => '.{6,}')))
                ->add('newpasssecond', 'password', array('label' => 'Confirmar:','attr' => array('pattern' => '.{6,}')))
                ->add('ssid', 'hidden', array('attr' => array('value' => $params)))
                ->add('enviar', 'submit', array('attr' => array('class' => 'button blue sub-btn')))
            ->getForm();
            
            /***** START VALIDATE *****/
            if ($msg = $this->valForm( $data )) {
                  return $this->render('CoreAdminBundle:login:register.html.twig', array( 'form' => $form->createView(), 'msg' => $msg, 'errors' => '', 'params' => $params));
              } 
            /***** END VALIDATE *****/

            $em = $this->getDoctrine()->getManager();
            $user = $em->getRepository('CoreAdminBundle:Users')->findOneBy(
                array(
                    'matricula' => $data['form']['matricula'],
                )
            );
            if ($user) {
                $user3 = $em->getRepository('CoreAdminBundle:Users')->findOneBy(
                    array(
                        'matricula' => $data['form']['matricula'],
                        'newpass' => '0'
                    )
                );
                if ($user3) {
                    $user2 = $em->getRepository('CoreAdminBundle:Users')->findOneBy(
                        array(
                            'username'  => $data['form']['username']
                        )
                    );
                    if (!$user2) {
                        $user->setNewpass($data['form']['newpass']);
                        $user->setUsername($data['form']['username']);
                        $user->setEmail($data['form']['email']);

                        $em->persist($user);
                        $em->flush();

                        $raduser = new Radcheck();
                        $raduser->setUsername($data['form']['username']);
                        $raduser->setAttribute('MD5-Password');
                        $raduser->setOp(':=');
                        $raduser->setValue($data['form']['newpass']);
                        $em->persist($raduser);
                        $em->flush();

                        $msg = "Tu registro se ha completado con éxito, ya puedes ingresar.";

                        $message = \Swift_Message::newInstance()
                            ->setSubject('Portal UVM :: Registro exitoso !')
                            ->setFrom(array('soporte@uvm.com' => 'Soporte'))
                            ->setTo($data['form']['email'])
                            ->setBody(
                                $this->renderView(
                                    'CoreAdminBundle:login:mailTamplateReg.html.twig',
                                    array('user' => $user->getUsername(),'pass' => $data['form']['newpass'] )
                                ), 'text/html'
                            )
                        ;
                        $this->get('mailer')->send($message);

                        return $this->render('CoreAdminBundle:login:plantilla.html.twig', array( 'user' => '', 'pass' => '', 'chk' => '', 'msg' => $msg, 'params' => $params ));
                    }else{
                        $msg = "El nombre de usuario seleccionado ya está utilizado por otra cuenta. Favor de elegir otro.";
                        return $this->render('CoreAdminBundle:login:register.html.twig', array( 'form' => $form->createView(), 'msg' => $msg, 'errors' => '', 'params' => $params ));
                    }
                }else{
                    $msg = "Esta matrícula ya fue registrada con anterioridad - Utiliza el usuario y password que registraste para unirte a la red. Si el problema persiste, contacta al Centro de Cómputo de tu campus.";
                    return $this->render('CoreAdminBundle:login:register.html.twig', array( 'form' => $form->createView(), 'msg' => $msg, 'errors' => '', 'params' => $params ));
                }
            }else{
                $msg = "Esta mátrícula no existe en el sistema. Favor de acudir al Centro de Cómputo para recibir asistencia.";
                return $this->render('CoreAdminBundle:login:register.html.twig', array( 'form' => $form->createView(), 'msg' => $msg, 'errors' => '', 'params' => $params ));
            }
        }else{
            return $this->render('CoreAdminBundle:login:register.html.twig', array( 'form' => $form->createView(), 'msg' => $msg, 'errors' => '', 'params' => $params ));
        }
    }
    /***************************************************************************/
    public function resetAction(Request $request)
    {
        $msg = '';

        if( $request->query->all() ){
            $params = '?';
            foreach ($request->query->all() as $key => $value) {
                $params .= $key.'='.$value.'&';
            }
        }else{
            $form = $request->request->get('form',NULL);
            $params = $form['genpass'];
        }

        $defaultData = array('message' => '');
        $form = $this->createFormBuilder($defaultData)
            ->setAction($this->generateUrl('portal_reset_pass'))
            ->add('username', 'text', array('label' => 'Usuario: ','attr' => array()))
            ->add('genpass', 'hidden', array('attr' => array('value' => $params)))
            ->add('email', 'email', array('label' => 'E-mail: ','attr' => array()))
            ->add('enviar', 'submit', array('attr' => array('class' => 'button blue sub-btn')))
        ->getForm();
        if ($request->isMethod('POST')) {
            $data = $request->request->all();

            $em = $this->getDoctrine()->getManager();
            $user = $em->getRepository('CoreAdminBundle:Users')->findOneBy(
                array(
                    'username' => $data['form']['username'],
                    'email' => $data['form']['email']
                )
            );
            if ($user) {
                $cadena = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz1234567890";
                $longitudCadena=strlen($cadena);
                $newpass = "";
                $longitudPass=8;
                for($i=1 ; $i<=$longitudPass ; $i++){
                    $pos=rand(0,$longitudCadena-1);
                    $newpass .= substr($cadena,$pos,1);
                }

                $user->setGenpass($newpass);
                $user->setNewpass('0');
                $em->persist($user);
                $em->flush();

                $message = \Swift_Message::newInstance()
                    ->setSubject('Portal UVM :: Reseteo de contraseña')
                    ->setFrom(array('soporte@uvm.com' => 'Soporte'))
                    ->setTo($data['form']['email'])
                    ->setBody(
                        $this->renderView(
                            'CoreAdminBundle:login:mailTamplate.html.twig',
                            array('user' => $user->getUsername(),'pass' => $newpass, 'hostname' => $_SERVER['HTTP_HOST'] )
                        ), 'text/html'
                    )
                ;
                $this->get('mailer')->send($message);

                $msg = 'Se ha enviado exitosamente un correo a su cuenta registrada. Favor de esperar 3 minutos para recibirlo y buscar en la carpeta de Correos NO Deseados (SPAM) en caso de no verlo en su carpeta de entrada (INBOX)';
                return $this->render('CoreAdminBundle:login:plantilla.html.twig', array( 'user' => '', 'pass' => '', 'chk' => '', 'msg' => $msg, 'params' => $params ));
            }else{
                $msg = 'La cuenta de correo y/o el usuario no se encuentran registrados en nuestro sistema';
                return $this->render('CoreAdminBundle:login:reset.html.twig', array( 'form' => $form->createView(), 'msg' => $msg, 'params' => $params ));
            }
        }else{
            return $this->render('CoreAdminBundle:login:reset.html.twig', array( 'form' => $form->createView(), 'msg' => $msg, 'params' => $params ));
        }
    }
    /***************************************************************************/
    public function changeAction(Request $request)
    {
        $msg = '';
        $usuario = new Users();
        $form = $this->createFormBuilder($usuario)
            ->setAction($this->generateUrl('portal_change_pass'))
            ->add('username', 'text', array('label' => 'Usuario: ','attr' => array('pattern' => '.{5,}')))
            ->add('matricula', 'text', array('label' => 'Matrícula: ','attr' => array()))
            ->add('genpass', 'text', array('label' => 'Clave: ','attr' => array('placeholder' => 'se te envio por e-mail', 'pattern' => '.{6,}')))
            ->add('newpass', 'password', array('label' => 'Password: ','attr' => array('placeholder' => 'nuevo password', 'pattern' => '.{6,}')))
            ->add('newpasssecond', 'password', array('label' => 'Confirmar: ','attr' => array('placeholder' => 'confirma el password', 'pattern' => '.{6,}')))
            ->add('enviar', 'submit', array('attr' => array('class' => 'button blue sub-btn')))
        ->getForm();

        if ($request->isMethod('POST')) {
            $data = $request->request->all();
            $em = $this->getDoctrine()->getManager();

            if( $data['form']['newpass'] === $data['form']['newpasssecond'] ){
                $user = $em->getRepository('CoreAdminBundle:Users')->findOneBy(
                    array(
                        'username'  => $data['form']['username'],
                        'matricula' => $data['form']['matricula'],
                        'genpass' => $data['form']['genpass'],
                        'newpass' => '0'
                    )
                );
                if ($user) {
                    $user->setGenpass('0');
                    $user->setNewpass($data['form']['newpass']);
                    $em->persist($user);
                    $em->flush();

                    $raduser = $em->getRepository('CoreAdminBundle:Radcheck')->findOneBy(
                        array(
                            'username'  => $data['form']['username']
                        )
                    );

                    if ($raduser) {
                        $raduser->setValue($data['form']['newpass']);
                        $em->persist($raduser);
                        $em->flush();
                    }

                    $msg = "Tu contraseña se ha cambiado con éxito, ya puedes ingresar.";
                    return $this->render('CoreAdminBundle:login:plantilla.html.twig', array( 'user' => '', 'pass' => '', 'chk' => '', 'msg' => $msg, 'params' => 0 ));
                }else{
                    $msg = "Datos Inválidos - Valida que tus datos sean correctos. Si el problema persiste, contacta al Centro de Cómputo de tu plantel.";
                    return $this->render('CoreAdminBundle:login:change.html.twig', array( 'form' => $form->createView(), 'msg' => $msg ));
                }
            }else{
                $msg = "Los campos de contraseña no coinciden, verifica e intenta de nuevo.";
                return $this->render('CoreAdminBundle:login:change.html.twig', array( 'form' => $form->createView(), 'msg' => $msg ));
            }
        }else{
            return $this->render('CoreAdminBundle:login:change.html.twig', array( 'form' => $form->createView(), 'msg' => $msg ));
        }
    }
    /***************************************************************************/
    public function setcookie($user = NULL, $pass = NULL, $campus = NULL, $chk_rec = NULL)
    {
        $response = new Response();

        if ($chk_rec == "chk_rec"){
            $user = htmlspecialchars(trim($user));
            $pass = trim($pass);

            $cookieU = new Cookie('usu', $user, time()+60*60*8);
            $cookieP = new Cookie('pass', $pass, time()+60*60*8);
            $cookieC = new Cookie('campus', $campus, time()+60*60*8);

            $response->headers->setCookie($cookieU);
            $response->headers->setCookie($cookieP);
            $response->headers->setCookie($cookieC);

            $response->send();

        }else{
            $response->headers->clearCookie('usu');
            $response->headers->clearCookie('pass');
            $response->headers->clearCookie('campus');
            $response->send();
        }
    }
    /***************************************************************************/
    public function getcookie($sesssion)
    {
        $account = array();
        $request = $this->get('request');
        if($request->cookies->has('usu') && $request->cookies->has('pass') ){
            $user = $request->cookies->get('usu');
            $pass = $request->cookies->get('pass');
            $campus = $request->cookies->get('campus');
            $sesssion->set('user_campus', $campus);
            $chk = "checked";
            $account['user'] = $user;
            $account['pass'] = $pass;
            return $account;
        }
        return false;
    }
    /***************************************************************************/
    public function formatMac($mac)
    {
        $longitud = strlen($mac);
            $macaddress = '';
            for($i = 0; $i < $longitud; ++$i)
                if($i%2==0)
                    if($i == 0)
                        $macaddress .= $mac[$i].$mac[$i+1];
                    else
                        $macaddress .= '-'.$mac[$i].$mac[$i+1];
        return $macaddress;
    }
    /***************************************************************************/
    public function valForm( $data )
    {
        if (!preg_match('/^[0-9]{3,}$/', $data['form']['matricula']) && $data['form']['matricula'] != '') {
            $msg = "El campo \"Matricula\" debe deben contener solo caracteres numéricos y por lo menos 4 caracteres de longitud";
            return $msg;
        }elseif(!preg_match("/^[a-z0-9]+[a-z0-9-_]+(\.[_a-zA-Z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*$/", $data['form']['email']) && $data['form']['email'] != ''){
            $msg = "El campo \"E-mail\" no contiene el formato adecuado";
            return $msg;
        }elseif(!preg_match("/^([a-zA-Z0-9])+([a-zA-Z0-9\._-])*@([a-zA-Z0-9_-])+([a-zA-Z0-9\._-]+)+$/", $data['form']['genpass']) && $data['form']['genpass'] != ''){
            $msg = "El campo \"Confirmación de E-mail\" no contiene el formato adecuado";
            return $msg;
        }elseif ( $data['form']['email'] !== $data['form']['genpass'] ) {
            $msg = "Los campos de \"E-mail\" deben coincidir.";
            return $msg;
        }elseif (!preg_match('/^[a-zA-Z0-9]{5,}$/', $data['form']['username']) && $data['form']['username'] != '') {
            $msg = "El campo \"Usuario\" debe contener solo caracteres alfanuméricos  y por lo menos 5 caracteres de longitud";
            return $msg;
        }elseif (!preg_match('/^[a-zA-Z0-9]{6,}$/', $data['form']['newpass']) && $data['form']['newpass'] != '') {
            $msg = "El campo \"contraseña\" debe contener solo caracteres alfanuméricos  y por lo menos 6 caracteres de longitud";
            return $msg;
        }elseif (!preg_match('/^[a-zA-Z0-9]{6,}$/', $data['form']['newpasssecond']) && $data['form']['newpasssecond'] != '') {
            $msg = "El campo \"confirmación de contraseña\" debe contener solo caracteres alfanuméricos  y por lo menos 6 caracteres de longitud";
            return $msg;
        }elseif ( $data['form']['newpass'] !== $data['form']['newpasssecond'] ) {
            $msg = "Los campos de \"contraseña\" deben coincidir.";
            return $msg;
        }
        return NULL;
    }
    /***************************************************************************/
}
