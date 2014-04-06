<?php

namespace Core\AdminBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\ORM\EntityManager;

use APY\DataGridBundle\Grid\Column;
use APY\DataGridBundle\Grid\Source\Vector;
use APY\DataGridBundle\Grid\Action\RowAction;

use Core\AdminBundle\Entity\Radcheck;
use Core\AdminBundle\Entity\Users;

class ReportsController extends Controller
{
########## REPORTS ##########
    /***************************************************************************/
    public function listregAction($session)
    {
        $em = $this->getDoctrine()->getManager();

        $sesssion = $this->getRequest()->getSession();
        $campus = $sesssion->get('session_nom');

        $usuarios = $em->createQueryBuilder()
          ->from('CoreAdminBundle:Radcheck', 'r')
          ->select("r.id,r.username,u.firstname,u.secondname,u.matricula,u.campus,u.tipo,s.macaddress as macaddress1,m.macaddress as macaddress2")
          ->leftJoin("CoreAdminBundle:Users", "u", "WITH", "r.username=u.username")
          ->leftJoin("CoreAdminBundle:Ssidmacauth", "s", "WITH", "s.username=u.username")
          ->leftJoin("CoreAdminBundle:Ssidmacauth", "m", "WITH", "m.username=u.username AND m.macaddress != s.macaddress")
          ->where("r.username != ''")
          ->groupBy("r.username")
        ->getQuery();

        $usuarios = $usuarios->getResult();
/*
        foreach ($usuarios as $usuario => $value) {
            $dql = "SELECT a.macaddress FROM CoreAdminBundle:Ssidmacauth a WHERE a.username='".$value['username']."'";
            $query = $em->createQuery($dql);
            $macaddress = $query->getResult();
            if($macaddress){
                $i = 1;
                foreach ($macaddress as $mac) {
                    $usuarios[$usuario]['macaddress'.$i] = $mac["macaddress"];
                    $i++;
                }
            }else{
                $usuarios[$usuario]['macaddress1'] = '';
                $usuarios[$usuario]['macaddress2'] = '';
            }
        }
*/
        $mensaje = '';

        $columns = array(
            new Column\NumberColumn(array('id' => 'id', 'field' => 'id', 'visible' => false, 'filterable' => false, 'source' => true, 'primary' => true, 'title' => 'id')),
            new Column\TextColumn(array('id' => 'username', 'filterable' => true, 'operatorsVisible' => false, 'filter' => 'input', 'size' => '-1', 'field' => 'username', 'source' => true, 'title' => 'Usuario')),
            new Column\TextColumn(array('id' => 'firstname', 'filterable' => true, 'operatorsVisible' => false, 'filter' => 'input', 'size' => '-1', 'field' => 'firstname', 'source' => true, 'title' => 'Nombre')),
            new Column\TextColumn(array('id' => 'secondname', 'filterable' => true, 'operatorsVisible' => false, 'filter' => 'input', 'size' => '-1', 'field' => 'secondname', 'source' => true, 'title' => 'Apellidos')),
            new Column\TextColumn(array('id' => 'matricula', 'filterable' => true, 'operatorsVisible' => false, 'filter' => 'input', 'size' => '-1', 'field' => 'matricula', 'source' => true, 'align' => 'center', 'title' => 'Matrícula')),
            new Column\TextColumn(array('id' => 'campus', 'filterable' => true, 'operatorsVisible' => false, 'filter' => 'input', 'size' => '-1', 'field' => 'campus', 'source' => true, 'align' => 'center', 'title' => 'Campus')),
            new Column\TextColumn(array('id' => 'tipo', 'filterable' => true, 'operatorsVisible' => false, 'filter' => 'input', 'size' => '-1', 'field' => 'tipo', 'source' => true, 'align' => 'center', 'title' => 'Tipo')),
            new Column\TextColumn(array('id' => 'macaddress1', 'filterable' => true, 'operatorsVisible' => false, 'filter' => 'input', 'size' => '-1', 'field' => 'macaddress1', 'source' => true, 'align' => 'center', 'title' => 'Macaddress1')),
            new Column\TextColumn(array('id' => 'macaddress2', 'filterable' => true, 'operatorsVisible' => false, 'filter' => 'input', 'size' => '-1', 'field' => 'macaddress2', 'source' => true, 'align' => 'center', 'title' => 'Macaddress2')),
        );

        $source = new Vector($usuarios,$columns);
        $grid = $this->get('grid');
        $grid->setSource($source);

        $myRowAction = new RowAction('Editar', 'admin_usuarios_modificar', false, '_self');
        $myRowAction->setRouteParameters(array('session' => $session, 'id', 'username' ));
        $grid->addRowAction($myRowAction);

        $myRowAction1 = new RowAction('Eliminar', 'admin_usuarios_eliminar', false, '_self');
        $myRowAction1->setRouteParameters(array('session' => $session, 'id' ));
        $grid->addRowAction($myRowAction1);

        $grid->setLimits(50);

        return $grid->getGridResponse('CoreAdminBundle:reports:listreg.html.twig', array());
    }
    /***************************************************************************/
    public function listunregAction($session)
    {
        $em = $this->getDoctrine()->getManager();

        $usuarios = $em->createQuery(
            "SELECT u.id,u.firstname,u.secondname,u.matricula,u.campus,u.tipo FROM CoreAdminBundle:Users u WHERE u.username != '' AND u.username = '---' ORDER BY u.firstname,u.secondname"
        );

        $usuarios = $usuarios->getResult();

        $mensaje = '';

        $columns = array(
            new Column\NumberColumn(array('id' => 'id', 'field' => 'id', 'visible' => false, 'filterable' => false, 'source' => true, 'primary' => true, 'title' => 'id')),
            new Column\TextColumn(array('id' => 'firstname', 'filterable' => true, 'operatorsVisible' => false, 'filter' => 'input', 'size' => '-1', 'field' => 'firstname', 'source' => true, 'title' => 'Nombre')),
            new Column\TextColumn(array('id' => 'secondname', 'filterable' => true, 'operatorsVisible' => false, 'filter' => 'input', 'size' => '-1', 'field' => 'secondname', 'source' => true, 'title' => 'Apellidos')),
            new Column\TextColumn(array('id' => 'matricula', 'filterable' => true, 'operatorsVisible' => false, 'filter' => 'input', 'size' => '-1', 'field' => 'matricula', 'source' => true, 'align' => 'center', 'title' => 'Matrícula')),
            new Column\TextColumn(array('id' => 'campus', 'filterable' => true, 'operatorsVisible' => false, 'filter' => 'input', 'size' => '-1', 'field' => 'campus', 'source' => true, 'align' => 'center', 'title' => 'Campus')),
            new Column\TextColumn(array('id' => 'tipo', 'filterable' => true, 'operatorsVisible' => false, 'filter' => 'input', 'size' => '-1', 'field' => 'tipo', 'source' => true, 'align' => 'center', 'title' => 'Tipo')),
        );

        $source = new Vector($usuarios,$columns);
        $grid = $this->get('grid');
        $grid->setSource($source);
        $grid->setLimits(50);

        $myRowAction = new RowAction('', 'admin_reportes_listar_unreg', false, '_self');
        $myRowAction->setRouteParameters(array('session' => $session ));
        $grid->addRowAction($myRowAction);

        return $grid->getGridResponse('CoreAdminBundle:reports:listunreg.html.twig', array());

    }
    /***************************************************************************/
	public function activeAction($session)
    {

        $em = $this->getDoctrine()->getManager();

        $query = $em->createQuery(
            "SELECT u.id,r.username,u.matricula,r.framedipaddress,r.calledstationid,SUM(r.acctinputoctets),SUM(r.acctoutputoctets),SUM(r.acctsessiontime)
            FROM CoreAdminBundle:Radacct r,CoreAdminBundle:Users u
            WHERE r.acctstoptime = '0000-00-00 00:00:00'  AND r.username = u.username
            GROUP BY r.username
            ORDER BY r.username ASC"
        );

        $usuarios = $query->getResult();

        $i=0;
        foreach ($usuarios as $usuario) {
        	$ssid =  explode(':',$usuario['calledstationid']);
        	$usuarios[$i]['calledstationid'] = $ssid['1'];
            $usuarios[$i]['acctsessiontime'] = $this->parseTime($usuario['3']);
            $usuarios[$i]['acctinputoctets'] = round($usuario['1'] * 0.000000953674316,1);
            $usuarios[$i]['acctoutputoctets'] = round($usuario['2'] * 0.000000953674316,1);
            $i++;
        }

        $columns = array(
            new Column\NumberColumn(array('id' => 'id', 'visible' => false, 'filterable' => true, 'operatorsVisible' => false, 'filter' => 'input', 'size' => '-1', 'field' => 'id', 'source' => true, 'align' => 'center', 'title' => 'ID')),
            new Column\TextColumn(array('id' => 'username', 'filterable' => true, 'operatorsVisible' => false, 'filter' => 'input', 'size' => '-1', 'field' => 'username', 'source' => true, 'title' => 'Usuario')),
            new Column\TextColumn(array('id' => 'matricula', 'filterable' => true, 'operatorsVisible' => false, 'filter' => 'input', 'size' => '-1', 'field' => 'matricula', 'source' => true, 'align' => 'center', 'title' => 'Matrícula')),
            new Column\TextColumn(array('id' => 'framedipaddress', 'filterable' => true, 'operatorsVisible' => false, 'filter' => 'input', 'size' => '-1', 'field' => 'framedipaddress', 'source' => true, 'align' => 'center', 'title' => 'IP')),
            new Column\TextColumn(array('id' => 'calledstationid', 'filterable' => true, 'operatorsVisible' => false, 'filter' => 'input', 'size' => '-1', 'field' => 'calledstationid', 'source' => true, 'align' => 'center', 'title' => 'SSID')),
            new Column\NumberColumn(array('id' => 'acctinputoctets', 'filterable' => true, 'operatorsVisible' => false, 'filter' => 'input', 'size' => '-1', 'field' => 'acctinputoctets', 'source' => true, 'align' => 'center', 'title' => 'KB Recibidos')),
            new Column\NumberColumn(array('id' => 'acctoutputoctets', 'filterable' => true, 'operatorsVisible' => false, 'filter' => 'input', 'size' => '-1', 'field' => 'acctoutputoctets', 'source' => true, 'align' => 'center', 'title' => 'KB Enviados')),
            new Column\TextColumn(array('id' => 'acctsessiontime', 'filterable' => true, 'operatorsVisible' => false, 'filter' => 'input', 'size' => '-1', 'field' => 'acctsessiontime', 'source' => true, 'align' => 'center', 'title' => 'T. Activo')),
            new Column\NumberColumn(array('id' => 1,'visible' => false)),
            new Column\NumberColumn(array('id' => 2,'visible' => false)),
            new Column\NumberColumn(array('id' => 3,'visible' => false)),
        );

        $source = new Vector($usuarios,$columns);
        $grid = $this->get('grid');
        $grid->setSource($source);
        $grid->setLimits(50);

        $myRowAction = new RowAction('', 'admin_reportes_listar_unreg', false, '_self');
        $myRowAction->setRouteParameters(array('session' => $session ));
        $grid->addRowAction($myRowAction);

        return $grid->getGridResponse('CoreAdminBundle:reports:active.html.twig', array());
    }
    /***************************************************************************/
    public function historyAction($session)
    {
        $em = $this->getDoctrine()->getManager();

        $query = $em->createQuery(
            "SELECT r.username,u.matricula,r.id,r.framedipaddress,r.calledstationid,SUM(r.acctinputoctets),SUM(r.acctoutputoctets),SUM(r.acctsessiontime)
            FROM CoreAdminBundle:Radacct r,CoreAdminBundle:Users u
            WHERE r.username = u.username
            GROUP BY r.username
            ORDER BY r.username ASC"
        );

        $usuarios = $query->getResult();

        $i=0;
        foreach ($usuarios as $usuario) {
        	$ssid =  explode(':',$usuario['calledstationid']);
        	$usuarios[$i]['calledstationid'] = $ssid['1'];
            $usuarios[$i]['acctsessiontime'] = $this->parseTime($usuario['3']);
            $usuarios[$i]['acctinputoctets'] = round($usuario['1'] * 0.000000953674316,1);
            $usuarios[$i]['acctoutputoctets'] = round($usuario['2'] * 0.000000953674316,1);
            $i++;
        }

        $columns = array(
            new Column\NumberColumn(array('id' => 'id', 'visible' => false, 'filterable' => true, 'operatorsVisible' => false, 'filter' => 'input', 'size' => '-1', 'field' => 'id', 'source' => true, 'align' => 'center', 'title' => 'ID')),
            new Column\TextColumn(array('id' => 'username', 'filterable' => true, 'operatorsVisible' => false, 'filter' => 'input', 'size' => '-1', 'field' => 'username', 'source' => true, 'title' => 'Usuario')),
            new Column\TextColumn(array('id' => 'matricula', 'filterable' => true, 'operatorsVisible' => false, 'filter' => 'input', 'size' => '-1', 'field' => 'matricula', 'source' => true, 'align' => 'center', 'title' => 'Matrícula')),
            new Column\TextColumn(array('id' => 'framedipaddress', 'filterable' => true, 'operatorsVisible' => false, 'filter' => 'input', 'size' => '-1', 'field' => 'framedipaddress', 'source' => true, 'align' => 'center', 'title' => 'IP')),
            new Column\TextColumn(array('id' => 'calledstationid', 'filterable' => true, 'operatorsVisible' => false, 'filter' => 'input', 'size' => '-1', 'field' => 'calledstationid', 'source' => true, 'align' => 'center', 'title' => 'SSID')),
            new Column\NumberColumn(array('id' => 'acctinputoctets', 'filterable' => true, 'operatorsVisible' => false, 'filter' => 'input', 'size' => '-1', 'field' => 'acctinputoctets', 'source' => true, 'align' => 'center', 'title' => 'KB Recibidos')),
            new Column\NumberColumn(array('id' => 'acctoutputoctets', 'filterable' => true, 'operatorsVisible' => false, 'filter' => 'input', 'size' => '-1', 'field' => 'acctoutputoctets', 'source' => true, 'align' => 'center', 'title' => 'KB Enviados')),
            new Column\TextColumn(array('id' => 'acctsessiontime', 'filterable' => true, 'operatorsVisible' => false, 'filter' => 'input', 'size' => '-1', 'field' => 'acctsessiontime', 'source' => true, 'align' => 'center', 'title' => 'T. Activo')),
            new Column\NumberColumn(array('id' => 1,'visible' => false)),
            new Column\NumberColumn(array('id' => 2,'visible' => false)),
            new Column\NumberColumn(array('id' => 3,'visible' => false)),
        );

        $source = new Vector($usuarios,$columns);
        $grid = $this->get('grid');
        $grid->setSource($source);
        $grid->setLimits(50);

        $myRowAction = new RowAction('', 'admin_reportes_listar_unreg', false, '_self');
        $myRowAction->setRouteParameters(array('session' => $session ));
        $grid->addRowAction($myRowAction);

        return $grid->getGridResponse('CoreAdminBundle:reports:history.html.twig', array());
    }
########## COMODIN FUNCTIONS  ##########
    /***************************************************************************/
    public function parseTime($segundos)
    {
        $minutos = $segundos/60;
        $horas = floor($minutos/60);
        $minutos2 = $minutos%60;
        $segundos_2 = $segundos%60%60%60;
        if($minutos2 < 10) $minutos2 = '0'.$minutos2;
        if($segundos_2 < 10) $segundos_2 = '0'.$segundos_2;
        if($segundos < 60){
            $resultado = round($segundos).' seg.';
        }elseif($segundos > 60 && $segundos < 3600){
            $resultado= $minutos2.':'.$segundos_2.' min.';
        }else{
            $resultado= $horas.':'.$minutos2.':'.$segundos_2.' hrs.';
        }
        return $resultado;
    }
##########  ##########
}
