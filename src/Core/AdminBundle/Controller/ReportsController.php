<?php

namespace Core\AdminBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\ORM\EntityManager;

use APY\DataGridBundle\Grid\Column;
use APY\DataGridBundle\Grid\Source\Vector;
use APY\DataGridBundle\Grid\Action\RowAction;
use APY\DataGridBundle\Grid\Column\ActionsColumn;
use APY\DataGridBundle\Grid\Export\ExcelExport;

use Core\AdminBundle\Entity\Radcheck;
use Core\AdminBundle\Entity\Users;

class ReportsController extends Controller
{
########## REPORTS ##########
    /***************************************************************************/
    public function indexAction(Request $request,$session,$type)
    {
        if ($request->isMethod('POST')) {
            $data = $request->request->all();
            if (isset($data['date1'])) {
                return $this->redirect( $this->generateUrl($type, array( 'session' => $session, 'campus' => $data['campus'], 'i' => $data['date1'], 'f' => $data['date2'] )) );
            }else{
                return $this->redirect( $this->generateUrl($type, array( 'session' => $session, 'campus' => $data['campus'] )) );
            }
        }

        $em = $this->getDoctrine()->getManager();

        $sesssion = $this->getRequest()->getSession();
        $nom = $sesssion->get('admin_nom');
        $session_id = $sesssion->get('session_id');

        if (!$session_id) {
            return $this->render('CoreAdminBundle:admin:index.html.twig', array( 'msg' => 'Su sesión se ha cerrado, ingrese de nuevo por favor.' ));
        }

        $query = $em->createQuery("SELECT c.nom,c.nombre FROM CoreAdminBundle:Campus c WHERE c.nom != 'UVM' AND c.activo = 1 ORDER BY c.nom ASC");
        $result = $query->getResult();

        return $this->render('CoreAdminBundle:reports:reports.html.twig', array( 'msg' => '', 'type' => $type, 'campus' => $result ));
    }
    /***************************************************************************/
    public function listunregAction(Request $request,$session,$campus)
    {
        $em = $this->getDoctrine()->getManager();
        $msg = '';

        $sesssion = $this->getRequest()->getSession();
        $nom = $sesssion->get('admin_nom');
        $session_id = $sesssion->get('session_id');

        if (!$session_id) {
            return $this->render('CoreAdminBundle:admin:index.html.twig', array( 'msg' => 'Su sesión se ha cerrado, ingrese de nuevo por favor.' ));
        }

        $where = " AND u.campus = '".$campus."' ";

        $usuarios = $em->createQuery(
            "SELECT u.id,u.firstname,u.secondname,u.matricula,u.campus,u.tipo FROM CoreAdminBundle:Users u WHERE u.username != '' AND u.username = '---' ".$where." ORDER BY u.firstname,u.secondname"
        );

        $usuarios = $usuarios->getResult();

        $num_res = count($usuarios);

        $mensaje = '';

        $columns = array(
            new Column\NumberColumn(array('id' => 'id', 'field' => 'id', 'visible' => false, 'filterable' => false, 'source' => true, 'primary' => true, 'title' => 'id')),
            new Column\TextColumn(array('id' => 'firstname', 'filterable' => true, 'operatorsVisible' => false, 'filter' => 'input', 'size' => '-1', 'field' => 'firstname', 'source' => true, 'title' => 'Nombre')),
            new Column\TextColumn(array('id' => 'secondname', 'filterable' => true, 'operatorsVisible' => false, 'filter' => 'input', 'size' => '-1', 'field' => 'secondname', 'source' => true, 'title' => 'Apellidos')),
            new Column\TextColumn(array('id' => 'matricula', 'filterable' => true, 'operatorsVisible' => false, 'filter' => 'input', 'size' => '-1', 'field' => 'matricula', 'source' => true, 'align' => 'center', 'title' => 'Matrícula')),
            new Column\TextColumn(array('id' => 'campus', 'filterable' => false, 'operatorsVisible' => false, 'filter' => 'select', 'size' => '-1', 'field' => 'campus', 'source' => true, 'align' => 'center', 'title' => 'Campus')),
            new Column\TextColumn(array('id' => 'tipo', 'filterable' => true, 'operatorsVisible' => false, 'filter' => 'select', 'selectFrom' => 'values', 'values' => array("ALUM"=>"Estudiantes","EMP"=>"Docentes"), 'size' => '-1', 'field' => 'tipo', 'source' => true, 'align' => 'center', 'title' => 'Tipo')),
        );

        $source = new Vector($usuarios,$columns);
        $grid = $this->get('grid');
        $grid->setSource($source);
        
        $grid->setLimits(array(25, 50, 100));
        $grid->setPage(1);

        if ($nom == 'UVM') {
            $grid->addExport(new ExcelExport('Exportar usuarios actuales', 'usuarios_pendientes'));
        }

        return $grid->getGridResponse('CoreAdminBundle:reports:listunreg.html.twig', array('msg' => $msg, 'num_res' => $num_res));
    }
    /***************************************************************************/
    public function listregAction(Request $request,$session,$campus)
    {
        $em = $this->getDoctrine()->getManager();
        $msg = '';
        $where = '';

        $sesssion = $this->getRequest()->getSession();
        $nom = $sesssion->get('admin_nom');
        $session_id = $sesssion->get('session_id');

        if (!$session_id) {
            return $this->render('CoreAdminBundle:admin:index.html.twig', array( 'msg' => 'Su sesión se ha cerrado, ingrese de nuevo por favor.' ));
        }

        $where = " AND u.campus = '".$campus."' ";

        $usuarios = $em->createQueryBuilder()
          ->from('CoreAdminBundle:Radcheck', 'r')
          ->select("r.id,r.username,u.firstname,u.secondname,u.email,u.matricula,u.ssid,s.macaddress as macaddress1,m.macaddress as macaddress2")
          ->leftJoin("CoreAdminBundle:Users", "u", "WITH", "r.username=u.username")
          ->leftJoin("CoreAdminBundle:Ssidmacauth", "s", "WITH", "s.username=r.username")
          ->leftJoin("CoreAdminBundle:Ssidmacauth", "m", "WITH", "m.username=r.username AND m.macaddress != s.macaddress")
          ->where("r.username != ''".$where)
          ->groupBy("r.username")
        ->getQuery();

        $usuarios = $usuarios->getResult();

        $num_res = count($usuarios);

        $columns = array(
            new Column\NumberColumn(array('id' => 'id', 'field' => 'id', 'visible' => false, 'filterable' => false, 'source' => true, 'primary' => true, 'title' => 'id')),
            new Column\TextColumn(array('id' => 'username', 'filterable' => true, 'operatorsVisible' => false, 'filter' => 'input', 'size' => '-1', 'field' => 'username', 'source' => true, 'title' => 'Usuario')),
            new Column\TextColumn(array('id' => 'firstname', 'filterable' => true, 'operatorsVisible' => false, 'filter' => 'input', 'size' => '-1', 'field' => 'firstname', 'source' => true, 'title' => 'Nombre')),
            new Column\TextColumn(array('id' => 'secondname', 'filterable' => true, 'operatorsVisible' => false, 'filter' => 'input', 'size' => '-1', 'field' => 'secondname', 'source' => true, 'title' => 'Apellidos')),
            new Column\TextColumn(array('id' => 'matricula', 'filterable' => true, 'operatorsVisible' => false, 'filter' => 'input', 'size' => '-1', 'field' => 'matricula', 'source' => true, 'align' => 'center', 'title' => 'Matrícula')),
            new Column\TextColumn(array('id' => 'email', 'filterable' => false, 'operatorsVisible' => false, 'filter' => 'input', 'size' => '-1', 'field' => 'email', 'source' => true, 'align' => 'center', 'title' => 'Email')),
            new Column\TextColumn(array('id' => 'ssid', 'filterable' => false, 'operatorsVisible' => false, 'filter' => 'input', 'size' => '-1', 'field' => 'ssid', 'source' => true, 'align' => 'center', 'title' => 'SSID')),
            new Column\TextColumn(array('id' => 'macaddress1', 'filterable' => true, 'operatorsVisible' => false, 'filter' => 'input', 'size' => '-1', 'field' => 'macaddress1', 'source' => true, 'align' => 'center', 'title' => 'Macaddress1')),
            new Column\TextColumn(array('id' => 'macaddress2', 'filterable' => true, 'operatorsVisible' => false, 'filter' => 'input', 'size' => '-1', 'field' => 'macaddress2', 'source' => true, 'align' => 'center', 'title' => 'Macaddress2')),
        );

        $source = new Vector($usuarios,$columns);
        $grid = $this->get('grid');
        $grid->setSource($source);

        $grid->setLimits(array(25, 50, 100));
        $grid->setPage(1);

        $actionsColumn = new ActionsColumn('actions', 'Acciones');
        $actionsColumn->setSeparator("|");
        $grid->addColumn($actionsColumn);

        $editRowAction = new RowAction('Editar', 'admin_usuarios_modificar', false, '_self');
        $editRowAction->setRouteParameters(array('session' => $session, 'id' ));
        $editRowAction->setColumn('actions');
        $grid->addRowAction($editRowAction);

        if ($nom == 'UVM') {
            $grid->addExport(new ExcelExport('Exportar usuarios actuales', 'usuarios_registrados'));

            $macRowAction = new RowAction('Macaddress', 'admin_usuarios_eliminar_macs', false, '_self');
            $macRowAction->setRouteParameters(array('session' => $session, 'id', 'campus' => $campus ));
            $macRowAction->setColumn('actions');
            $grid->addRowAction($macRowAction);
        }

        $delRowAction = new RowAction('Eliminar', 'admin_usuarios_eliminar', false, '_self');
        $delRowAction->setRouteParameters(array('session' => $session, 'id', 'campus' => $campus ));
        $delRowAction->setColumn('actions');
        $grid->addRowAction($delRowAction);

        return $grid->getGridResponse('CoreAdminBundle:reports:listreg.html.twig', array('msg' => $msg, 'num_res' => $num_res));
    }
    /***************************************************************************/
	public function activeAction(Request $request,$session,$campus,$i,$f)
    {

        $em = $this->getDoctrine()->getManager();

        $sesssion = $this->getRequest()->getSession();
        $nom = $sesssion->get('admin_nom');
        $session_id = $sesssion->get('session_id');

        if (!$session_id) {
            return $this->render('CoreAdminBundle:admin:index.html.twig', array( 'msg' => 'Su sesión se ha cerrado, ingrese de nuevo por favor.' ));
        }

        $where = " AND u.campus = '".$campus."' ";

        $query = $em->createQuery(
            "SELECT u.id,r.username,u.matricula,u.campus,r.framedipaddress,r.calledstationid,SUM(r.acctinputoctets),SUM(r.acctoutputoctets),SUM(r.acctsessiontime)
            FROM CoreAdminBundle:Radacct r,CoreAdminBundle:Users u
            WHERE r.acctstoptime = '0000-00-00 00:00:00' AND r.username = u.username ".$where." AND ( r.acctstarttime BETWEEN '".$i."' AND '".$f."' )
            GROUP BY r.username
            ORDER BY r.username ASC"
        );

        $q = "SELECT u.id,r.username,u.matricula,u.campus,r.framedipaddress,r.calledstationid,SUM(r.acctinputoctets),SUM(r.acctoutputoctets),SUM(r.acctsessiontime)
            FROM CoreAdminBundle:Radacct r,CoreAdminBundle:Users u
            WHERE r.acctstoptime = '0000-00-00 00:00:00' AND r.username = u.username ".$where." AND ( r.acctstarttime BETWEEN '".$i."' AND '".$f."' )
            GROUP BY r.username
            ORDER BY r.username ASC";

        // var_dump($q);

        $usuarios = $query->getResult();

        $num_res = count($usuarios);

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
            new Column\TextColumn(array('id' => 'campus', 'filterable' => false, 'operatorsVisible' => false, 'filter' => 'input', 'size' => '-1', 'field' => 'campus', 'source' => true, 'align' => 'center', 'title' => 'Campus')),
            new Column\TextColumn(array('id' => 'calledstationid', 'filterable' => false, 'operatorsVisible' => false, 'filter' => 'input', 'size' => '-1', 'field' => 'calledstationid', 'source' => true, 'align' => 'center', 'title' => 'SSID')),
            new Column\NumberColumn(array('id' => 'acctinputoctets', 'filterable' => false, 'operatorsVisible' => false, 'filter' => 'input', 'size' => '-1', 'field' => 'acctinputoctets', 'source' => true, 'align' => 'center', 'title' => 'KB Recibidos')),
            new Column\NumberColumn(array('id' => 'acctoutputoctets', 'filterable' => false, 'operatorsVisible' => false, 'filter' => 'input', 'size' => '-1', 'field' => 'acctoutputoctets', 'source' => true, 'align' => 'center', 'title' => 'KB Enviados')),
            new Column\TextColumn(array('id' => 'acctsessiontime', 'filterable' => false, 'operatorsVisible' => false, 'filter' => 'input', 'size' => '-1', 'field' => 'acctsessiontime', 'source' => true, 'align' => 'center', 'title' => 'T. Activo')),
            new Column\NumberColumn(array('id' => 1,'visible' => false)),
            new Column\NumberColumn(array('id' => 2,'visible' => false)),
            new Column\NumberColumn(array('id' => 3,'visible' => false)),
        );

        $source = new Vector($usuarios,$columns);
        $grid = $this->get('grid');
        $grid->setSource($source);
        
        $grid->setLimits(array(25, 50, 100));
        $grid->setPage(1);

        if ($nom == 'UVM') {
            $grid->addExport(new ExcelExport('Exportar usuarios actuales', 'usuarios_activos'));
        }

        return $grid->getGridResponse('CoreAdminBundle:reports:active.html.twig', array('num_res' => $num_res));
    }
    /***************************************************************************/
    public function historyAction(Request $request,$session,$campus,$i,$f)
    {
        $em = $this->getDoctrine()->getManager();

        $sesssion = $this->getRequest()->getSession();
        $nom = $sesssion->get('admin_nom');
        $session_id = $sesssion->get('session_id');

        if (!$session_id) {
            return $this->render('CoreAdminBundle:admin:index.html.twig', array( 'msg' => 'Su sesión se ha cerrado, ingrese de nuevo por favor.' ));
        }

        $where = " AND u.campus = '".$campus."' ";

        $query = $em->createQuery(
            "SELECT r.username,u.matricula,u.campus,r.id,r.framedipaddress,r.calledstationid,SUM(r.acctinputoctets),SUM(r.acctoutputoctets),SUM(r.acctsessiontime)
            FROM CoreAdminBundle:Radacct r,CoreAdminBundle:Users u
            WHERE r.username = u.username ".$where." AND ( r.acctstarttime BETWEEN '".$i."' AND '".$f."' )
            GROUP BY r.username
            ORDER BY r.username ASC"
        );

        $q = "SELECT r.username,u.matricula,u.campus,r.id,r.framedipaddress,r.calledstationid,SUM(r.acctinputoctets),SUM(r.acctoutputoctets),SUM(r.acctsessiontime)
            FROM CoreAdminBundle:Radacct r,CoreAdminBundle:Users u
            WHERE r.username = u.username ".$where." AND ( r.acctstarttime BETWEEN '".$i."' AND '".$f."' )
            GROUP BY r.username
            ORDER BY r.username ASC";

        // var_dump($q);

        $usuarios = $query->getResult();

        $num_res = count($usuarios);

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
            new Column\TextColumn(array('id' => 'campus', 'filterable' => false, 'operatorsVisible' => false, 'filter' => 'input', 'size' => '-1', 'field' => 'campus', 'source' => true, 'align' => 'center', 'title' => 'Campus')),
            new Column\TextColumn(array('id' => 'calledstationid', 'filterable' => false, 'operatorsVisible' => false, 'filter' => 'input', 'size' => '-1', 'field' => 'calledstationid', 'source' => true, 'align' => 'center', 'title' => 'SSID')),
            new Column\NumberColumn(array('id' => 'acctinputoctets', 'filterable' => false, 'operatorsVisible' => false, 'filter' => 'input', 'size' => '-1', 'field' => 'acctinputoctets', 'source' => true, 'align' => 'center', 'title' => 'KB Recibidos')),
            new Column\NumberColumn(array('id' => 'acctoutputoctets', 'filterable' => false, 'operatorsVisible' => false, 'filter' => 'input', 'size' => '-1', 'field' => 'acctoutputoctets', 'source' => true, 'align' => 'center', 'title' => 'KB Enviados')),
            new Column\TextColumn(array('id' => 'acctsessiontime', 'filterable' => false, 'operatorsVisible' => false, 'filter' => 'input', 'size' => '-1', 'field' => 'acctsessiontime', 'source' => true, 'align' => 'center', 'title' => 'T. Activo')),
            new Column\NumberColumn(array('id' => 1,'visible' => false)),
            new Column\NumberColumn(array('id' => 2,'visible' => false)),
            new Column\NumberColumn(array('id' => 3,'visible' => false)),
        );

        $source = new Vector($usuarios,$columns);
        $grid = $this->get('grid');
        $grid->setSource($source);
        
        $grid->setLimits(array(25, 50, 100));
        $grid->setPage(1);

        if ($nom == 'UVM') {
            $grid->addExport(new ExcelExport('Exportar usuarios actuales', 'usuarios_historial'));
        }

        return $grid->getGridResponse('CoreAdminBundle:reports:history.html.twig', array('num_res' => $num_res));
    }
    /***************************************************************************/
    public function maintenanceAction(Request $request,$session,$campus)
    {
        $em = $this->getDoctrine()->getManager();

        $sesssion = $this->getRequest()->getSession();
        $nom = $sesssion->get('admin_nom');
        $session_id = $sesssion->get('session_id');

        if (!$session_id) {
            return $this->render('CoreAdminBundle:admin:index.html.twig', array( 'msg' => 'Su sesión se ha cerrado, ingrese de nuevo por favor.' ));
        }

        $where = " AND u.campus = '".$campus."' ";

        $query = $em->createQuery(
            "SELECT u.id,u.username,u.firstname,u.secondname,u.email,u.matricula,u.campus,u.tipo,u.ssid
            FROM CoreAdminBundle:Users u
            WHERE u.username != '---' ".$where."
            ORDER BY u.username ASC"
        );

        $usuarios = $query->getResult();

        $num_res = count($usuarios);

        $columns = array(
            new Column\NumberColumn(array('id' => 'id', 'visible' => false, 'filterable' => true, 'operatorsVisible' => false, 'filter' => 'input', 'size' => '-1', 'field' => 'id', 'source' => true, 'align' => 'center', 'title' => 'ID')),
            new Column\TextColumn(array('id' => 'username', 'filterable' => true, 'operatorsVisible' => false, 'filter' => 'input', 'size' => '-1', 'field' => 'username', 'source' => true, 'title' => 'Usuario')),
            new Column\TextColumn(array('id' => 'firstname', 'filterable' => true, 'operatorsVisible' => false, 'filter' => 'input', 'size' => '-1', 'field' => 'firstname', 'source' => true, 'align' => 'center', 'title' => 'Nombre')),
            new Column\TextColumn(array('id' => 'secondname', 'filterable' => true, 'operatorsVisible' => false, 'filter' => 'input', 'size' => '-1', 'field' => 'secondname', 'source' => true, 'align' => 'center', 'title' => 'Apellidos')),
            new Column\TextColumn(array('id' => 'email', 'filterable' => true, 'operatorsVisible' => false, 'filter' => 'input', 'size' => '-1', 'field' => 'email', 'source' => true, 'align' => 'center', 'title' => 'Email')),
            new Column\TextColumn(array('id' => 'matricula', 'filterable' => true, 'operatorsVisible' => false, 'filter' => 'input', 'size' => '-1', 'field' => 'matricula', 'source' => true, 'align' => 'center', 'title' => 'Matrícula')),
            new Column\TextColumn(array('id' => 'campus', 'filterable' => false, 'operatorsVisible' => false, 'filter' => 'input', 'size' => '-1', 'field' => 'campus', 'source' => true, 'align' => 'center', 'title' => 'Campus')),
            new Column\TextColumn(array('id' => 'tipo', 'filterable' => false, 'operatorsVisible' => false, 'filter' => 'input', 'size' => '-1', 'field' => 'tipo', 'source' => true, 'align' => 'center', 'title' => 'Tipo')),
            new Column\TextColumn(array('id' => 'ssid', 'filterable' => false, 'operatorsVisible' => false, 'filter' => 'input', 'size' => '-1', 'field' => 'ssid', 'source' => true, 'align' => 'center', 'title' => 'Ssid')),
        );

        $source = new Vector($usuarios,$columns);
        $grid = $this->get('grid');
        $grid->setSource($source);
        
        $grid->setLimits(array(25, 50, 100));
        $grid->setPage(1);

        $editRowAction = new RowAction('Limpiar', 'admin_usuarios_limpiar', false, '_self');
        $editRowAction->setRouteParameters(array('session' => $session, 'id', 'campus' => $campus ));
        $editRowAction->setColumn('actions');
        $grid->addRowAction($editRowAction);

        return $grid->getGridResponse('CoreAdminBundle:reports:maintenance.html.twig', array('num_res' => $num_res));
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
