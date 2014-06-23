<?php

namespace Core\AdminBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;
use Doctrine\Common\Collections;

use APY\DataGridBundle\Grid\Column;
use APY\DataGridBundle\Grid\Source\Vector;
use APY\DataGridBundle\Grid\Action\RowAction;

use Core\AdminBundle\Entity\Users;
use Core\AdminBundle\Entity\Radcheck;
use Core\AdminBundle\Entity\Ssidmacauth;
use Core\AdminBundle\Entity\Campus;

class UsersController extends Controller
{
########## USERS ##########
    public function newAction(Request $request,$session)
    {
        /*************************************************/
        /** START Session
        /*************************************************/
        $request = Request::createFromGlobals();
        $sesssion = $this->getRequest()->getSession();
        $session_id = $sesssion->get('session_id');
        $nombre = $sesssion->get('admin_nombre');
        $nom = $sesssion->get('admin_nom');
        $campus = array();
        $msg = '';
        if (!$session_id) {
            return $this->render('CoreAdminBundle:admin:index.html.twig', array( 'msg' => 'Su sesión se ha cerrado, ingrese de nuevo por favor.' ));
        }
        /*************************************************/
        /** END Session
        /*************************************************/

        $em = $this->getDoctrine()->getManager();

        if ($nom == 'UVM') {
            $query = $em->createQuery("SELECT c.nom FROM CoreAdminBundle:Campus c WHERE c.nom != 'UVM' ORDER BY c.nom ASC");
            $result = $query->getResult();
            foreach ($result as $value) {
                $campus[$value['nom']] = $value['nom'];
            }
        }else{
            $campus[$nom] = $nom;
        }

        $usuario = new Users();
        $usuario->setFecha( new \DateTime('today') );

        $form = $this->createFormBuilder($usuario)
            ->setAction($this->generateUrl('admin_usuarios_crear', array('session' => $session)))
            ->add('firstname', 'text', array('label' => 'Nombre','attr' => array('placeholder' => 'Nombre')))
            ->add('secondname', 'text', array('label' => 'Apellidos','attr' => array('placeholder' => 'Apellidos')))
            ->add('username', 'hidden', array('attr' => array('value' => '---')))
            ->add('matricula', 'text', array('label' => 'Matricula','attr' => array('placeholder' => 'matricula')))
            ->add('campus', 'choice', array('label' => 'Campus', 'choices' => $campus, 'attr' => array('placeholder' => 'campus')))
            ->add('tipo', 'choice', array('label' => 'Tipo', 'choices' => array('ALUM' => 'ALUMNO','EMP' => 'EMPLEADO'), 'attr' => array('placeholder' => 'tipo')))
            ->add('ssid', 'hidden', array('attr' => array('value' => '0')))
            ->add('genpass', 'hidden', array('attr' => array('value' => '0')))
            ->add('newpass', 'hidden', array('attr' => array('value' => '0')))
            ->add('newpasssecond', 'hidden', array('attr' => array('value' => '0')))
            ->add('email', 'hidden', array('attr' => array('value' => 'uvm@uvm.com')))
            ->add('enviar', 'submit')
        ->getForm();

        if ($request->isMethod('POST')) {

            $data = $request->request->all();

            /*************************************************/
            /** START Validacion
            /*************************************************/
            if ($msg = $this->valForm2( $data )) {
                  return $this->render('CoreAdminBundle:users:new.html.twig', array( 'form' => $form->createView(), 'msg' => $msg ));
              }
            /*************************************************/
            /** END Validacion
            /*************************************************/

            $em = $this->getDoctrine()->getManager();
            $user = $em->getRepository('CoreAdminBundle:Users')->findOneBy(
                array(
                    'matricula'  => $data['form']['matricula']
                )
            );
            if ($user) {
                $msg = 'La matricula que ingreso ya existe en el sistema.';
            }else{
                $user = new Users();
                $user->setFirstname($data['form']['firstname']);
                $user->setSecondname($data['form']['secondname']);
                $user->setUsername('---');
                $user->setMatricula($data['form']['matricula']);
                $user->setCampus($data['form']['campus']);
                $user->setTipo($data['form']['tipo']);
                if ($data['form']['tipo'] == 'ALUM') {
                    $user->setSsid('UVM '.$data['form']['campus'].' ESTUDIANTES');
                }elseif ($data['form']['tipo'] == 'EMP') {
                    $user->setSsid('UVM '.$data['form']['campus'].' DOCENTES');
                }
                $user->setGenpass(0);
                $user->setNewpass(0);
                $user->setNewpasssecond(0);
                $user->setEmail('');
                $user->setFecha( new \DateTime('today') );
                $em->persist($user);
                $em->flush();
                $msg = 'El usuario se ha agregado con éxito.';
            }
        }

        return $this->render('CoreAdminBundle:users:new.html.twig', array( 'form' => $form->createView(), 'msg' => $msg ));
    }
    /***************************************************************************/
    public function editAction($session,$id)
    {
        /*************************************************/
        /** START Session
        /*************************************************/
        $request = Request::createFromGlobals();
        $sesssion = $this->getRequest()->getSession();
        $session_id = $sesssion->get('session_id');
        $nombre = $sesssion->get('admin_nombre');
        $nom = $sesssion->get('admin_nom');
        $campus = array();
        $msg = '';

        if (!$session_id) {
            return $this->render('CoreAdminBundle:admin:index.html.twig', array( 'msg' => 'Su sesión ha caducado, ingrese de nuevo por favor.' ));
        }
        /*************************************************/
        /** END Session
        /*************************************************/

        $em = $this->getDoctrine()->getManager();

        if ($nom == 'UVM') {
            $query = $em->createQuery("SELECT c.nom FROM CoreAdminBundle:Campus c WHERE c.nom != 'UVM' ORDER BY c.nom ASC");
            $result = $query->getResult();
            foreach ($result as $value) {
                $campus[$value['nom']] = $value['nom'];
            }
        }else{
            $campus[$nom] = $nom;
        }

        $user_form = new Users();
        $form = $this->createFormBuilder($user_form);

        $usuario = $em->getRepository('CoreAdminBundle:Radcheck')->find($id);
        $username = $usuario->getUsername();

        $usuario = $em->getRepository('CoreAdminBundle:Users')->findOneBy(
            array(
                'username'  => $username
            )
        );

    	$user_form->setFirstname( $usuario->getFirstname() );
            $user_form->setSecondname( $usuario->getSecondname() );
            $user_form->setMatricula( $usuario->getMatricula() );
            $user_form->setEmail( $usuario->getEmail() );
            $user_form->setUsername( $usuario->getUsername() );
            $user_form->setCampus( $usuario->getCampus() );
            $user_form->setTipo( $usuario->getTipo() );
            $user_form->setNewpass( $usuario->getNewpass() );

        $form = $this->createFormBuilder($user_form)
            ->setAction( $this->generateUrl('admin_usuarios_modificar', array( 'session' => $session, 'id' => $id, 'username' => $username )) )
            ->add('firstname', 'text', array('label' => 'Nombre','attr' => array('placeholder' => 'Nombre')))
            ->add('secondname', 'text', array('label' => 'Apellidos (paterno y materno separados por un espacio)','attr' => array('placeholder' => 'Apellidos')))
            ->add('matricula', 'text', array('label' => 'Matricula','attr' => array('placeholder' => 'Matricula')))
            ->add('campus', 'choice', array('label' => 'Campus', 'choices' => $campus, 'data' => $usuario->getCampus(), 'attr' => array('placeholder' => 'campus')))
            ->add('tipo', 'choice', array('label' => 'Tipo', 'choices' => array('ALUM' => 'ALUMNO','EMP' => 'EMPLEADO'), 'attr' => array('placeholder' => 'tipo')))
            ->add('email', 'email', array('label' => 'E-mail','attr' => array('placeholder' => 'correo electronico')))
            ->add('username', 'text', array('label' => 'Usuario (elije un nombre de usuario de por lo menos 5 caracteres)','attr' => array('placeholder' => 'Mínimo de 5 caracteres.')))
            ->add('newpass', 'text', array('label' => 'Password (mínimo 6 caracteres, no se diferencian mayúsculas de minúsculas y utiliza solo caracteres alfanuméricos.)','attr' => array('placeholder' => 'Mínimo de 6 caracteres.', 'pattern' => '.{6,}')))
            ->add('enviar', 'submit')
        ->getForm();

        if ($request->isMethod('POST')) {

            $data = $request->request->all();

            /*************************************************/
            /** START Validacion
            /*************************************************/
            if ($msg = $this->valForm( $data )) {
                  return $this->render('CoreAdminBundle:users:edit.html.twig', array( 'form' => $form->createView(),'session' => $session, 'session_id' => $session, 'usuario' => $usuario, 'msg' => $msg, 'campus' => $nom ));
              } 
            /*************************************************/
            /** END Validacion
            /*************************************************/
            $usuario = $em->getRepository('CoreAdminBundle:Radcheck')->find($id);
            $usuario1 = $em->getRepository('CoreAdminBundle:Users')->findOneBy(
                array(
                    'username'  => $username
                )
            );

            $usuarioR = $em->getRepository('CoreAdminBundle:Users')->findBy(
                array(
                    'username'  => $username
                )
            );

            var_dump( count( $usuarioR ) );

            $msg = "El nombre de usuario seleccionado ya está utilizado por otra cuenta. Favor de elegir otro.";

            if (count($usuarioR) > 1) {
                return $this->render('CoreAdminBundle:users:edit.html.twig', array( 'form' => $form->createView(),'session' => $session, 'session_id' => $session, 'usuario' => $usuario, 'msg' => $msg, 'campus' => $nom ));
            }


            $usuario2 = $em->getRepository('CoreAdminBundle:Ssidmacauth')->findBy(
                array(
                    'username'  => $username,
                )
            );
            if($usuario && $usuario1){
                $usuario->setUsername($data['form']['username']);
                $usuario->setValue($data['form']['newpass']);

                $em->persist($usuario);
                $em->flush();

                $usuario1->setFirstname( $data['form']['firstname'] );
                $usuario1->setSecondname( $data['form']['secondname'] );
                $usuario1->setMatricula( $data['form']['matricula'] );
                $usuario1->setCampus($data['form']['campus']);
                $usuario1->setTipo($data['form']['tipo']);
                if ($data['form']['tipo'] == 'ALUM') {
                    $usuario1->setSsid('UVM '.$data['form']['campus'].' ESTUDIANTES');
                }elseif ($data['form']['tipo'] == 'EMP') {
                    $usuario1->setSsid('UVM '.$data['form']['campus'].' DOCENTES');
                }
                $usuario1->setEmail( $data['form']['email'] );
                $usuario1->setUsername( $data['form']['username'] );
                $usuario1->setNewpass( $data['form']['newpass'] );

                $em->persist($usuario1);
                $em->flush();

                if($usuario2){
                    foreach ($usuario2 as $usuario) {
                        $usuario->setUsername($data['form']['username']);
                        $em->persist($usuario);
                        $em->flush();
                    }
                }
                $msg = 'Usuario modificado con éxito !';
                $usuario = $em->getRepository('CoreAdminBundle:Users')->findOneBy(
                    array(
                        'username'  => $data['form']['username']
                    )
                );

                $user_form->setFirstname( $usuario->getFirstname() );
                $user_form->setSecondname( $usuario->getSecondname() );
                $user_form->setMatricula( $usuario->getMatricula() );
                $user_form->setEmail( $usuario->getEmail() );
                $user_form->setUsername( $usuario->getUsername() );
                $user_form->setCampus( $usuario->getCampus() );
                $user_form->setTipo( $usuario->getTipo() );
                $user_form->setNewpass( $usuario->getNewpass() );

                $form = $this->createFormBuilder($user_form)
                    ->setAction( $this->generateUrl('admin_usuarios_modificar', array( 'session' => $session, 'id' => $id, 'username' => $username )) )
                    ->add('firstname', 'text', array('label' => 'Nombre','attr' => array('placeholder' => 'Nombre')))
                    ->add('secondname', 'text', array('label' => 'Apellidos (paterno y materno separados por un espacio)','attr' => array('placeholder' => 'Apellidos')))
                    ->add('matricula', 'text', array('label' => 'Matricula','attr' => array('placeholder' => 'Matricula')))
                    ->add('campus', 'choice', array('label' => 'Campus', 'choices' => $campus, 'data' => $usuario->getCampus(), 'attr' => array('placeholder' => 'campus')))
                    ->add('tipo', 'choice', array('label' => 'Tipo', 'choices' => array('ALUM' => 'ALUMNO','EMP' => 'EMPLEADO'), 'attr' => array('placeholder' => 'tipo')))
                    ->add('email', 'email', array('label' => 'E-mail','attr' => array('placeholder' => 'correo electronico')))
                    ->add('username', 'text', array('label' => 'Usuario (elije un nombre de usuario de por lo menos 5 caracteres)','attr' => array('placeholder' => 'Mínimo de 5 caracteres.')))
                    ->add('newpass', 'text', array('label' => 'Password (mínimo 6 caracteres, no se diferencian mayúsculas de minúsculas y utiliza solo caracteres alfanuméricos.)','attr' => array('placeholder' => 'Mínimo de 6 caracteres.', 'pattern' => '.{6,}')))
                    ->add('enviar', 'submit')
                ->getForm();
            }
        }

       return $this->render('CoreAdminBundle:users:edit.html.twig', array( 'form' => $form->createView(),'session' => $session, 'session_id' => $session, 'usuario' => $usuario, 'msg' => $msg, 'campus' => $campus ));
    }
    /***************************************************************************/
    public function delAction($session,$id,$campus)
    {
        $request = Request::createFromGlobals();
        $confirm = $request->request->get('confirm',NULL);

        $em = $this->getDoctrine()->getManager();

        $usuario_radchek = $em->getRepository('CoreAdminBundle:Radcheck')->find($id);
        $usuario_ssidmacauth = $em->getRepository('CoreAdminBundle:Ssidmacauth')->findBy(array('username' => $usuario_radchek->getUsername()));
        $usuario_users = $em->getRepository('CoreAdminBundle:Users')->findOneBy(array('username' => $usuario_radchek->getUsername()));


        if($confirm){
            $em->remove($usuario_radchek);
            $em->flush();

            foreach ($usuario_ssidmacauth as $user) {
                $em->remove($user);
                $em->flush();
            }

            $usuario_users->setUsername('---');
            $usuario_users->setGenpass(0);
            $usuario_users->setNewpass(0);
            $usuario_users->setEmail('');

            $em->persist($usuario_users);
            $em->flush();

            return $this->redirect( $this->generateUrl('admin_reportes_listar_reg', array( 'session' => $session, 'campus' => $campus )) );
        }else{
            $mensaje = "¿Seguro que desea eliminar al usuario '".$usuario_radchek->getUsername()."' ?";
        }
        return $this->render('CoreAdminBundle:users:del.html.twig', array( 'session' => $session, 'session_id' => $session, 'msg' => $mensaje, 'usuario' => $usuario_radchek, 'campus' => $campus ));
    }
    /***************************************************************************/
    public function clearAction($session,$id,$campus)
    {
        $em = $this->getDoctrine()->getManager();

        $usuario = $em->getRepository('CoreAdminBundle:Users')->find($id);
        $usuario_radchek = $em->getRepository('CoreAdminBundle:Radcheck')->findOneBy(array('username' => $usuario->getUsername()));

        if($usuario_radchek){
            $em->remove($usuario_radchek);
            $em->flush();
        }

        $usuario->setUsername('---');
        $usuario->setGenpass(0);
        $usuario->setNewpass(0);
        $usuario->setEmail('');

        $em->persist($usuario);
        $em->flush();

        return $this->redirect( $this->generateUrl('admin_reportes_mantenimiento', array( 'session' => $session, 'campus' => $campus, 'msg' => '' )) );
    }
    /***************************************************************************/
    public function delmacAction($session,$id,$campus)
    {
        $request = Request::createFromGlobals();
        $confirm = $request->request->get('confirm',NULL);

        $em = $this->getDoctrine()->getManager();

        $usuario_radchek = $em->getRepository('CoreAdminBundle:Radcheck')->find($id);
        $usuario_ssidmacauth = $em->getRepository('CoreAdminBundle:Ssidmacauth')->findBy(array('username' => $usuario_radchek->getUsername()));

        foreach ($usuario_ssidmacauth as $user) {
            $em->remove($user);
            $em->flush();
        }

        $msg = 'Se realizo la limpieza con éxito !';

        return $this->redirect( $this->generateUrl('admin_reportes_listar_reg', array( 'session' => $session, 'campus' => $campus )) );
    }
    /***************************************************************************/
    public function resetmacsAction()
    {
        $em = $this->getDoctrine()->getManager();
        $conn = $em->getConnection();

        $conn->exec('TRUNCATE TABLE ssidmacauth');

        return $this->render('CoreAdminBundle:users:resetmacs.html.twig', array( 'fecha' => date('d-M-Y H:m a') ));

    }
    /***************************************************************************/
    public function valForm( $data )
    {
        if (!preg_match('/^[a-zA-Z ]{3,}$/', $data['form']['firstname']) && $data['form']['firstname'] != '') {
            $msg = "Los campos \"nombre\" y \"Apellidos\" deben contener solo caracteres alfabéticos y por lo menos 3 caracteres de longitud";
            return $msg;
        }elseif (!preg_match('/^[a-zA-Z ]{3,}$/', $data['form']['secondname']) && $data['form']['secondname'] != '') {
            $msg = "Los campos \"nombre\" y \"Apellidos\" deben contener solo caracteres alfabéticos y por lo menos 3 caracteres de longitud";
            return $msg;
        }elseif (!preg_match('/^[0-9]{4,}$/', $data['form']['matricula']) && $data['form']['matricula'] != '') {
            $msg = "El campo \"Matricula\" debe deben contener solo caracteres numéricos y por lo menos 4 caracteres de longitud";
            return $msg;
        }elseif(!filter_var($data['form']['email'], FILTER_VALIDATE_EMAIL) && $data['form']['email'] != ''){
            $msg = "El campo \"E-mail\" no contiene el formato adecuado";
            return $msg;
        }elseif (!preg_match('/^[a-zA-Z0-9]{5,10}$/', $data['form']['username']) && $data['form']['username'] != '') {
            $msg = "El campo \"Usuario\" debe contener solo caracteres alfanuméricos, por lo menos 5 caracteres de longitud y un máximo de 10 caracteres";
            return $msg;
        }elseif (!preg_match('/^[a-zA-Z0-9]{6,12}$/', $data['form']['newpass']) && $data['form']['newpass'] != '') {
            $msg = "El campo \"contraseña\" debe contener solo caracteres alfanuméricos  y por lo menos 6 caracteres de longitud y un máximo de 12 caracteres";
            return $msg;
        }
        return NULL;
    }
    /***************************************************************************/
    public function valForm2( $data )
    {
        if (!preg_match('/^[a-zA-Z ]{3,}$/', $data['form']['firstname']) && $data['form']['firstname'] != '') {
            $msg = "Los campos \"nombre\" y \"Apellidos\" deben contener solo caracteres alfabéticos y por lo menos 3 caracteres de longitud";
            return $msg;
        }elseif (!preg_match('/^[a-zA-Z ]{3,}$/', $data['form']['secondname']) && $data['form']['secondname'] != '') {
            $msg = "Los campos \"nombre\" y \"Apellidos\" deben contener solo caracteres alfabéticos y por lo menos 3 caracteres de longitud";
            return $msg;
        }elseif (!preg_match('/^[0-9]{4,}$/', $data['form']['matricula']) && $data['form']['matricula'] != '') {
            $msg = "El campo \"Matricula\" debe deben contener solo caracteres numéricos y por lo menos 4 caracteres de longitud";
            return $msg;
        }
        return NULL;
    }
    /***************************************************************************/

##########  ##########
}
