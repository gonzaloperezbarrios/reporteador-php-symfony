<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

use AppBundle\Entity\Prueba;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\ResetType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;






class PruebaController extends Controller
{
    /**
     * @Route("/tabla/", name="TablaGenrico")
     */
    public function tablaAction(Request $request)
    {   
        $em = $this->getDoctrine()->getEntityManager();
        $connection = $em->getConnection();
        $statement = $connection->prepare("
                        SELECT                        
                        prueba.Nombre AS '0',
                        prueba.Sexo AS '1',
                        prueba.Cargo AS '2',
                        prueba.Edad AS '3',
                        prueba.Salario AS '4'
                        FROM
                        prueba
                        ");  
        $statement->execute();
        $constantes = $statement->fetchAll();  
        $columnas = array('Nombre',
                        'Sexo',
                        'Cargo',
                        'Edad',
                        'Salario' ); 
       $info = array('paginaTitulo' => 'Tabla generica', 
                      'tablaTitulo' =>'Tabla generica',
                      'tablaSubTitulo' => 'Data por SQL',
                      'tablaInfo' =>'Esta tabla puede ser cargada dinamicamente por cualquier sentecia SQL (o un array asociativo) siempre cuando los nombres del SELECT SQL esten enumerodados de cero en adelante (0,1,2,3..)' 
                     );    
       return $this->render('generico/tabla.html.twig',array(
                                                               'columnas'=>$columnas,
                                                               'filas' =>$constantes,
                                                               'numfila'=>count($constantes)-1,
                                                               'numcolumna'=>count($columnas)-1,
                                                               'info'=>$info
                                                              
                                                               ));
    }
            
      /**
     * @Route("/constantes/list/{id}", name="Detalle_Constantes")
     */
    public function listAction($id)
    {
        $em = $this->getDoctrine()->getEntityManager();
        $connection = $em->getConnection();
        $statement = $connection->prepare("SELECT
                        constantes.id,
                        constantes.Nombre AS nombre,
                        constantes.Utilidad AS utilidad,
                        constantes.Plazo AS plazo,
                        constantes.Dias_Clavo AS diasClavo,
                        constantes.id_Oficina AS idOficina,                        
                        oficina.Nombre_Oficina AS nombreOficina
                        FROM
                        constantes 
                        INNER JOIN oficina ON constantes.id_Oficina=oficina.id
                        WHERE constantes.id=:id");            
        $statement->bindValue('id', $id);
        $statement->execute();
        $constante = $statement->fetchAll();        
        return $this->render('constantes/list.html.twig', array('constante' =>$constante['0']));
    }
    
    /**
     * @Route("/formulario/add/", name="Crear_Formulario")
     */
    public function addAction(Request $request)
    {   
 
        $form = $this->createFormBuilder()
        
            ->add('Texto', TextType::class,array('label' => 'Texto *', 
                                                'label_attr' => array('class' => 'control-label col-md-3 col-sm-3 col-xs-12'),
                                                'attr' => array('class' => 'col-md-7 col-xs-12')))
            ->add('Numero', IntegerType::class,array('label' => 'Numero *', 
                                                    'label_attr' => array('class' => 'control-label col-md-3 col-sm-3 col-xs-12'),
                                                    'attr' => array('class' => 'col-md-7 col-xs-12')))
           
            ->add('TextoArea', TextareaType::class,array('label' => 'Text Area *', 
                                                         'label_attr' => array('class' => 'control-label col-md-3 col-sm-3 col-xs-12'),
                                                         'attr' => array('class' => 'col-md-7 col-xs-12')))
            
       
            ->add('isAttending', ChoiceType::class, array(
                                                    'choices'  => array(
                                                                        'Maybe' => null,
                                                                        'Yes' => true,
                                                                        'No' => false,
                                                                        ),    
                                                 'label' => 'Check List *',                                                
                                                 'label_attr' => array('class' => 'control-label col-md-3 col-sm-3 col-xs-12'),
                                                 'attr' => array('class' => 'col-md-7 col-xs-12')
                                                 )) 
             ->add('isAttending33', ChoiceType::class, array(
                                                    'choices'  => array(
                                                                        'Maybe' => null,
                                                                        'Yes' => true,
                                                                        'No' => false,
                                                                        ),    
                                                 'label' => 'Check List *',                                                
                                                 'label_attr' => array('class' => 'control-label col-md-3 col-sm-3 col-xs-12'),
                                                 'attr' => array('class' => 'select2_single col-md-7 col-xs-12')
                                                 ))                                                                                    
            ->add('public', CheckboxType::class, array(                                                
                                                'required' => false,
                                                 'label' => 'Single Check *',                                                 
                                                 'label_attr' => array('class' => 'control-label col-md-3 col-sm-3 col-xs-12'),
                                                 'attr' => array('class' => 'flat')
                                                 )) 
                                                 
         
            ->add('attending2', ChoiceType::class, array(
                                                    'choices' => array(
                                                        'Yes' => true,
                                                        'No' => false,
                                                        'Maybe' => null,
                                                    ),
                                                    'choices_as_values' => true,
                                                    'expanded' => true,
                                                    'multiple' => false,
                                                    'label_attr' => array('class' => 'control-label col-md-3 col-sm-3 col-xs-12'),
                                                    'choice_attr' => function($val, $key, $index) {
                                                        // adds a class like attending_yes, attending_no, etc
                                                        return ['class' => 'flat'];
                                                    },
                                                ))                 
                             
                                                              
            ->add('Fecha', TextType::class,array('label' => 'Fecha *',
                                            'label_attr' => array('class' => 'control-label col-md-3 col-sm-3 col-xs-12'),
                                            'attr' => array('class' => 'date-picker-control col-md-7 col-xs-12')
                                            ))   
                                                 
                    // ->add('cancelar', ResetType::class, array('label' => 'Cancelar','attr' => array('class' => 'btn btn-success')))
            
            ->getForm();  
         $form->handleRequest($request);         
         if ($form->isSubmitted() && $form->isValid()) 
         {    
            
             
             $this->addFlash(
               'notice',
               'Guardado.'  
             );                     
          
        }              
        return $this->render('generico/formulario.html.twig', array(
            'form' => $form->createView(),
             'formularioTitulo' => 'Formulario'
                 ));
    }
    
    
    /**
     * @Route("/constantes/update/{id}/{id_ofi}", name="Actualizar_Constantes")
     */
    public function updateAction($id,$id_ofi,Request $request)
    {
        $constante=$this->getDoctrine()
              ->getRepository('AppBundle:Constantes')
              ->find($id);            
         
        $form = $this->createFormBuilder($constante)
            ->add('nombre', TextType::class)  
            ->add('utilidad', IntegerType::class)
            ->add('plazo', IntegerType::class)
            ->add('diasClavo', IntegerType::class)                   
            ->add('idOficina', EntityType::class, array( 
                    'label'=>'Oficina',                  
                    'class' => 'AppBundle:Oficina',
                    'choice_label' => 'nombreOficina',                   
                ))        
            ->add('save', SubmitType::class, array('label' => 'Editar Constante','attr' => array('class' => 'ui-widget-header ui-corner-all editar')))
            ->getForm();
            
         $form->handleRequest($request);
         
         if ($form->isSubmitted() && $form->isValid()) 
         {  
             $constante->setIdOficina((int)$_POST['form']['idOficina']);             
             $em=$this->getDoctrine()->getManager();             
             $em->flush();              
             
             $this->addFlash(
               'notice',
               'Actualizado.'  
             );         
            return $this->redirectToRoute('Constantes');
        } 
             
        return $this->render('constantes/update.html.twig', array(
            'form' => $form->createView(),'oficina'=>$id_ofi
        ));
           
    }
    
     /**
     * @Route("/constantes/delete/{id}", name="Borrar_Constantes")
     */
    public function deleteOficinaAction($id)
    {
        $em = $this->getDoctrine()->getManager();
        $oficina = $em->getRepository('AppBundle:Constantes')->find($id);

        if (!$oficina) {
            throw $this->createNotFoundException(
                'No product found for id '.$id
            );
        }

        $em->remove($oficina);
        $em->flush();

        return $this->redirectToRoute('Constantes');
    }
    
      /**
     * @Route("/capas/consulta/{id}", name="Query_Capa")
     */
    public function consultaCapaAction($id)
    {
        
        $em = $this->getDoctrine()->getManager();
        $query = $em->createQuery(
            'SELECT
            c
            FROM AppBundle:Capa c
            WHERE c.colorCapa=:price'  
        )->setParameter('price', $id);

        $capas = $query->getResult();
        //var_dump($products);
        //die('fin query');
        return $this->render('capa/index.html.twig', array('capas' =>$capas));
    }
    public function add(){
         $fecha=new\DateTime('now');
             $abonoPrestamo->setIdPrestamo($id);
             $abonoPrestamo->setFecha($fecha);
             $abonoPrestamo->setIdUsuario(111);
             
             $em=$this->getDoctrine()->getManager();
             $em->persist($abonoPrestamo);
             $em->flush();
    }
    
  
}
