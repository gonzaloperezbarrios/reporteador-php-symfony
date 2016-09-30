<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\ResetType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;

use Doctrine\DBAL\DriverManager;

use AppBundle\Entity\Group;

class Sys_GroupController extends Controller
{
     //Este metodo se volverar generico 
     //carga la informacion y la muestra en un array asociativo
    private function newTabla($sql,$msm=true)
    {
        //Data de la consulta
        //Select filas
        try {
            $em = $this->getDoctrine()->getEntityManager();
            $connection = $em->getConnection();         
            $statement = $connection->prepare($sql);  
            $statement->execute();
            $filasx = $statement->fetchAll(); 
        } catch (\Exception $e) {
                if($msm){
                        $this->addFlash(
                        'error',
                        'Su sentencia SQL,no es correcta. Revísela y vuelva a intentarlo.'  
                        ); 
                }
                return array(  
                            'control'=>0              
                           );
        }        
        $filas=array();
        $columnas=array();
        //Renombra las filas y columnas
        for($i=0;$i<count($filasx);$i++)
        {
            $j=0;
            foreach ($filasx[$i] as $clave => $valor) {    
                    $valor=strtolower($valor);                 
                    $filas[$i][$j]=$valor;                    
                    //Renombra las columnas
                    if($i==0)
                    {
                        $columnas[$j]=$clave;
                    }
                    $j++;
                
            } 
        }      

        //informacion de la data de la tabla
        $infoTabla=array('filas'=>$filas,
                            'columnas'=>$columnas,
                            'lengthColumnas'=>count($columnas)-1,   
                            'lengthFilas'=>count($filas)-1           
        );      

        if($msm){
            $this->addFlash(
                'info',
                'Reporte creado correctamente.'  
                );
        }    
        return array(                                                                      
                    'infoTabla'=>$infoTabla ,      
                    'control'=>5              
                    );
    
    }    

     /**
     * @Route("/grupo/", name="adminrol")
     */
      public function indexction(Request $request)
    {          
               
        $form = $this->createFormBuilder() 
            ->add('name', TextType::class,array('label' => 'Rol *', 
                                                'label_attr' => array('class' => 'control-label col-md-4 col-sm-4 col-xs-12'),
                                                'attr' => array('class' => 'col-md-7 col-xs-10')))
            ->add('nombre', EntityType::class, array( 
                    'label'=>'Permiso *', 
                    'multiple'=>true,                 
                    'class' => 'AppBundle:Sys_Rol',
                    'choice_label' => 'Permiso',  
                    'label_attr' => array('class' => 'control-label col-md-4 col-sm-4 col-xs-12'),
                    'attr' => array('class' => 'col-md-7 col-xs-10')                 
                ))  
                         
            ->getForm();  
         $form->handleRequest($request);         
         if ($form->isSubmitted() && $form->isValid()) 
         {                         
            
             $grupo=$request->get('form')['name'];
             $arrayRolesPost=$request->get('form')['nombre'];  
             $arrayRoles=array();
             foreach ($arrayRolesPost as $key => $id) {                 
                 $em = $this->getDoctrine()->getManager();
                 $a = $em->getRepository('AppBundle:Sys_Rol')->find($id);
                 array_push($arrayRoles,$a->getNombre());
             }                   
             $this->newGrupo($grupo,$arrayRoles);             
                         
                
            $this->addFlash(
                            'info',
                            'Rol creado con éxito.'  
                            ); 
            
            return $this->redirectToRoute('adminrol');   
           
            
                             
          
        }   
         //Informacion de las paginas            
        $fecha=strftime("El día, %d del mes %m del %Y %H:%M");		
        $info = array('pagina'=>array(
                      'titulo' => 'Permisos y Roles',
                    ),                    
                      'formulario'=>array(
                      'titulo' => 'Nuevo Rol', 
                      'subtitulo' =>'Grupo permisos'
                    ),
                      'tabla_crud'=>array(
                      'titulo' => 'Roles', 
                      'subtitulo' =>'Autorización sistema',
                      'descripcion'=>'Generado: '.$fecha                      
                    ),
                    'tabla'=>array(
                      'titulo' => 'Detalle', 
                      'subtitulo' =>'Permisos',
                      'descripcion'=>'registrado: Permiso por defecto de los usuarios registrados.'
                    ),
                      'grafica'=>array(
                      'titulo' => '', 
                      'subtitulo' =>': '.$fecha
                    )
        ); 
        $usuario = $this->get('security.token_storage')->getToken()->getUser();
        $id_usuario=$usuario->getId();  
        $sql="SELECT id, name AS 'Rol', roles AS 'Permiso' FROM fos_group";  
        $retorno=$this->newTabla($sql,false);   
        $infoTabla=$retorno['infoTabla'];  
         
        foreach ($infoTabla['filas'] as $key => $value) {
           $a=unserialize($infoTabla['filas'][$key][2]);
           $text='registrado';
           if(!empty($a)){
               foreach ($a as $key2 => $value2) {
                   $value2=explode("_", $value2);
                   $text.=' '.$value2[1];
               }             
           }
           $infoTabla['filas'][$key][2]=$text;
           
           
        }     

        $sql="SELECT Permiso,Descripcion AS Descripción FROM sys_rol";  
        $r=$this->newTabla($sql,false);   
        $infoTabla2=$r['infoTabla'];   
         
        if(empty($infoTabla['filas'])){
            $control=0;
        }
        else{           
            $control=$retorno['control'];
        }
        
        return $this->render('sys_group/index.html.twig', array(
                                                                    'form' => $form->createView(),
                                                                    'info'=>$info,
                                                                    'infoTabla_crud'=>$infoTabla, 
                                                                    'infoTabla'=>$infoTabla2, 
                                                                    'control'=> $control                                                          
                                                        
                                                                ));
    }


    /**
     * @Route("/grupo/delete/{id}/", name="BorrarGrupo")
     */
    public function deleteAction($id)
    {
        $control=true;
        $msmError='No se pudo borrar el rol.\nRecargue la pagina y vuelva a intentarlo.' ;
        try {
                $em = $this->getDoctrine()->getManager();
                $grupo = $em->getRepository('AppBundle:Group')->find($id);

                if (!$grupo) {
                    $this->addFlash(
                                    'advertencia',
                                    'Este rol ya habia sido borrado.'  
                                   );
                }               

                $em->remove($grupo);
                $em->flush();
        } catch (\Exception $e) {
            if($e->getErrorCode()==1451)
            {
                $msmError='No se puede borrar este rol, porque esta asignados a usuarios del sistema. Elimine o edite los usurios asignados a este rol antes borrarlo.';
            }
            
           $control=false;
               
   }
   
    if($control){
        $this->addFlash(
                        'info',
                        'Se ha borrado con éxito el rol.'  
                        );
    }
    else{
         $this->addFlash('error',$msmError);

    }
          
         

        return $this->redirectToRoute('adminrol');   
    }


     /**
     * @Route("/grupo/update/{id}", name="ActualizarGrupo")
     */
    public function updateAction($id,Request $request)
    {
           
        $em = $this->getDoctrine()->getManager();
        $grupo = $em->getRepository('AppBundle:Group')->find($id);   
        $form = $this->createFormBuilder() 
            ->add('name', TextType::class,array('label' => 'Rol *', 
                                                'label_attr' => array('class' => 'control-label col-md-4 col-sm-4 col-xs-12'),
                                                'attr' => array('class' => 'col-md-7 col-xs-10')))
           ->add('roles_old', TextType::class,array('label' => 'Permisos actuales', 
                                                'disabled'=>true,
                                                'label_attr' => array('class' => 'control-label col-md-4 col-sm-4 col-xs-12'),
                                                'attr' => array('class' => 'cajaLabel col-md-7 col-xs-10')))
                            
            ->add('nombre', EntityType::class, array( 
                    'label'=>'Permisos *', 
                    'multiple'=>true,                 
                    'class' => 'AppBundle:Sys_Rol',
                    'choice_label' => 'Permiso',  
                    'label_attr' => array('class' => 'control-label col-md-4 col-sm-4 col-xs-12'),
                    'attr' => array('class' => 'col-md-7 col-xs-10')                 
                ))  
                         
            ->getForm();
            
         $form->handleRequest($request);
         
         if ($form->isSubmitted() && $form->isValid()) 
         {    
             $usuario = $this->get('security.token_storage')->getToken();
             var_dump( $usuario);
             die(' update...');
             $idTipoConexion=$request->get('form')['idTipoConexion'];
             
             $driver=$this->getDoctrine()
                             ->getRepository('AppBundle:Sys_TipoConexion')
                             ->find($idTipoConexion);              
                             
             $user=$request->get('form')['user'];
             $port=$request->get('form')['port'];
             $password=$request->get('form')['password'];
             $host=$request->get('form')['host'];
             $dbname=$request->get('form')['nameBD'];
             $valConexion=$this->validarConexion($driver->getdriver(),$user,$port,$password,$host,$dbname);
             if( $valConexion)
             { 
                 //Seteamos
                $sysConexionBD->setIdTipoConexion($idTipoConexion);
                //Update 
                $em=$this->getDoctrine()->getManager();             
                $em->flush();              
                
                $this->addFlash(
                                    'info',
                                    'Conexión de la BD externa, actualizada correctamente.'  
                                ); 
                            
                return $this->redirectToRoute('Crear_Conexion_BD');   
             }
             else {
                   $this->addFlash(
                                'error',
                                'No se pudo establecer conexión, con la base de datos externa.\nRevise los datos de conexión y vuelva a intentarlo.'  
                                ); 
                
             }
        } 
        
        //Informacion de las paginas            
        $fecha=strftime("El día, %d del mes %m del %Y %H:%M");		
        $info = array('pagina'=>array(
                      'titulo' => 'Actualizar Rol',
                    ),                    
                      'formulario'=>array(
                      'titulo' => 'Actualizar Rol', 
                      'subtitulo' =>'Grupo permisos'
                    ),
                     'tabla'=>array(
                      'titulo' => 'Detalle', 
                      'subtitulo' =>'Permisos',
                      'descripcion'=>'registrado: Permiso por defecto de los usuarios registrados.'
                    ),
                      'grafica'=>array(
                      'titulo' => '', 
                      'subtitulo' =>': '.$fecha
                    )
        );       
       
       $text='registrado';  
       foreach ($grupo->getRoles() as $key => $value) {  
            if(!empty($value)){   
                    $value=explode("_", strtolower($value));
                    $text.=' '.$value[1];              
            }
       }  

       $sql="SELECT Permiso,Descripcion AS Descripción FROM sys_rol";  
        $r=$this->newTabla($sql,false);   
        $infoTabla2=$r['infoTabla'];   
           
           
                    
        return $this->render('sys_group/update.html.twig', array(
                                                                    'form' => $form->createView(),
                                                                    'info'=>$info,
                                                                    'infoTabla'=>$infoTabla2, 
                                                                    'textRoles'=>$text,
                                                                    'grupoName'=>$grupo->GetName()
                                                                   ));
           
    }


    private function newGrupo($name,$arrayRoles)
    { 
            $grupo=new Group($name,$arrayRoles);               
            $em=$this->getDoctrine()->getManager();
            $em->persist($grupo);
            $em->flush();   
            
    }


}
