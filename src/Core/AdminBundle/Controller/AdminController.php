<?php

namespace Core\AdminBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\Session\Session;

use Core\AdminBundle\Entity\Campus;

class AdminController extends Controller
{
########## ADMINISTRATOR ##########
    public function adminAction()
    {
        $sesssion = $this->getRequest()->getSession();
        $sesssion->invalidate();

        return $this->render('CoreAdminBundle:admin:index.html.twig', array( 'msg' => '' ));
    }
    /***************************************************************************/
    public function loginAction()
    {
        $sesssion = new Session();
        $sesssion->start();

        $request = Request::createFromGlobals();
        $user = $request->request->get('user',NULL);
        $pass = $request->request->get('pass',NULL);

        $pass = md5($pass.'uVm');

        $em = $this->getDoctrine()->getManager();

        $admin = $em->getRepository('CoreAdminBundle:Campus')->findOneBy(
            array(
                'usuario'  => $user,
                'clave'  => $pass
            )
        );

        if ($admin) {
            if ($admin->getActivo() == 1) {
                $session = base64_encode( md5( $admin->getNombre().$admin->getNom().date('Y-n-d') ) );
                $sesssion->set('admin_nombre', $admin->getNombre());
                $sesssion->set('admin_nom', $admin->getNom());
                $sesssion->set('session_id', $session);

                return $this->redirect( $this->generateUrl('admin_home', array( 'session' => $session )) );
            }else{
                return $this->render('CoreAdminBundle:admin:index.html.twig', array( 'msg' => 'Esta cuenta se encuetra deshabilitada.' ));
            }
        }else{
            return $this->render('CoreAdminBundle:admin:index.html.twig', array( 'msg' => 'Usuario y/o contraseña iválidos.' ));
        }
    }
    /***************************************************************************/
    public function homeAction($session)
    {
        $sesssion = $this->getRequest()->getSession();
        $nombre = $sesssion->get('admin_nombre');
        $nom = $sesssion->get('admin_nom');

        //$session_id = base64_encode( md5( $nombre.$nom.date('Y-n-d') ) );

        if ( $sesssion->get('admin_nom') ) {
            return $this->render('CoreAdminBundle:admin:lpg.html.twig', array());
        }else{
            return $this->render('CoreAdminBundle:admin:index.html.twig', array( 'msg' => 'Su sesión ha caducado, ingrese de nuevo por favor.' ));
        }
    }
########## ##########
}
