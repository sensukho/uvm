<?php

namespace Core\AdminBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use APY\DataGridBundle\Grid\Column;
use APY\DataGridBundle\Grid\Source\Vector;
use APY\DataGridBundle\Grid\Action\RowAction;
use Core\AdminBundle\Entity\radcheck;
use Core\AdminBundle\Entity\Users;

class ReportsController extends Controller
{
########## REPORTS ##########
	public function activeAction($session)
    {
        $sesssion = $this->getRequest()->getSession();
        $campus = $sesssion->get('session_admin');

        $em = $this->getDoctrine()->getManager();

        switch ( $campus ) {
            case "all":
                $where_campus = "";
            break;

            case $campus:
                $where_campus = "AND u.campus = '".$campus."'";
            break;

            default:
                $where_campus = "";
            break;
        }

        $query = $em->createQuery(
            "SELECT u.id,r.username,u.matricula,r.framedipaddress,r.calledstationid,SUM(r.acctinputoctets),SUM(r.acctoutputoctets),SUM(r.acctsessiontime)
            FROM CoreAdminBundle:radacct r,CoreAdminBundle:Users u
            WHERE r.acctstoptime = '0000-00-00 00:00:00'  AND r.username = u.username ".$where_campus."
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
            new Column\NumberColumn(array('id' => 'id', 'filterable' => true, 'operatorsVisible' => false, 'filter' => 'input', 'size' => '-1', 'field' => 'id', 'source' => true, 'align' => 'center', 'title' => 'ID')),
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

        $myRowAction = new RowAction('', 'admin_usuarios_listar_unreg', false, '_self');
        $myRowAction->setRouteParameters(array('session' => $session, 'q' => '0', 'offset' => '1' ));
        $grid->addRowAction($myRowAction);

        return $grid->getGridResponse('CoreAdminBundle:reports:active.html.twig', array( 'session' => $session, 'session_id' => $session, 'usuarios' => $usuarios, 'campus' => $campus ));

    }
    /***************************************************************************/
    public function historyAction($session)
    {
        $sesssion = $this->getRequest()->getSession();
        $campus = $sesssion->get('session_admin');

        $em = $this->getDoctrine()->getManager();

        switch ( $campus ) {
            case "all":
                $where_campus = "";
            break;

            case $campus:
                $where_campus = "AND u.campus = '".$campus."'";
            break;

            default:
                $where_campus = "";
            break;
        }

        $query = $em->createQuery(
            "SELECT r.username,r.id,r.framedipaddress,r.calledstationid,SUM(r.acctinputoctets),SUM(r.acctoutputoctets),SUM(r.acctsessiontime)
            FROM CoreAdminBundle:radacct r,CoreAdminBundle:Users u
            WHERE r.username = u.username  ".$where_campus."
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
            new Column\NumberColumn(array('id' => 'id', 'filterable' => true, 'operatorsVisible' => false, 'filter' => 'input', 'size' => '-1', 'field' => 'id', 'source' => true, 'align' => 'center', 'title' => 'ID')),
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

        $myRowAction = new RowAction('', 'admin_usuarios_listar_unreg', false, '_self');
        $myRowAction->setRouteParameters(array('session' => $session, 'q' => '0', 'offset' => '1' ));
        $grid->addRowAction($myRowAction);

        return $grid->getGridResponse('CoreAdminBundle:reports:history.html.twig', array( 'session' => $session, 'session_id' => $session, 'usuarios' => $usuarios, 'campus' => $campus ));

        return $this->render('CoreAdminBundle:reports:history.html.twig', array( 'session' => $session, 'session_id' => $session, 'usuarios' => $usuarios, 'campus' => $campus ));
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
