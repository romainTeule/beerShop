<?php


use Monolog\Logger;
use Monolog\Handler\StreamHandler;

// create a log channel
$log = new Logger('name');
$log->pushHandler(new StreamHandler(__DIR__ .'/products.log', Logger::WARNING));

$app->get('/manageProducts[/{offset}]',manageProducts)->setName('manageProducts');



function manageProducts($request,$response, $args){
     global $twig;
       global $entityManager;
     $products;
     $offset;
     
     
     if($args['offset'])
     {
        $offset=   $args['offset'];
     }
     else
     {
         $offset=0;
     }
     //get products list
     
     $productRepository = $entityManager->getRepository('Product');
        // $count=$productRepository->count();
        
    $queryBuilder = $entityManager->createQueryBuilder();

    $queryBuilder->select('COUNT(u.id)')
    ->from(Product::class, 'u');

    $query = $queryBuilder->getQuery();
       
    $count=$query->getSingleScalarResult();   
        
     $products=$productRepository->findBy(array(),array('name' => 'ASC'), 5, $offset);


    $params= array('title' => 'Gestion catalogue','products'=>$products, 'crtOffset'=>$offset, 'count'=>$count);
    $template= $twig -> loadTemplate('manageProducts.twig');
    
    return $response->write($template->render($params));
}


$app->post('/updateProduct[/{id}]',updateProduct)->setName('updateProduct');



function updateProduct($request,$response, $args){
   global $twig;
   global $entityManager;
   global $app;
    $productRepository = $entityManager->getRepository('Product');
       
    if($args['id'])
     {
        $product=$productRepository->findOneBy(array('id' =>
                                              $args['id']));
     }
     else
     {
        $product= new Product;
     
     }
       
    
   $allPostPutVars = $request->getParsedBody();
       
       
    $product->setName($allPostPutVars[nom]);
    $product->setDescription($allPostPutVars[description]);
    $product->setPrice($allPostPutVars[prix]);
        
    $entityManager->persist($product);
    $entityManager->flush();
         
         
    $response = $response->withRedirect($app->getContainer()->get('router')->pathFor('manageProducts', [], [
    'offset' => 0,
    ]));
      
    return $response;
}



$app->get('/naviguateCatalogue[/{offset}]',naviguateCatalogue)->setName('naviguateCatalogue');


function naviguateCatalogue($request,$response, $args){
     global $twig;
     global $entityManager;
     $products;
     $offset;
     
     $name = $request->getParam('nom');
     $priceMin = $request->getParam('prixMin');
    $priceMax = $request->getParam('prixMax');
     
     if($args['offset'])
     {
        $offset=$args['offset'];
     }
     else
     {
         $offset=0;
     }
     //get products list
     
     
    $productRepository = $entityManager->getRepository('Product');
        // $count=$productRepository->count();
        
    $queryBuilder = $entityManager->createQueryBuilder();

    $queryBuilder->select(' u')
    ->from(Product::class, 'u');

    $queryBuilderCount = $entityManager->createQueryBuilder();

    $queryBuilderCount->select('Count(u.id)')
    ->from(Product::class, 'u');
    
    
    if($name)
    {
        $queryBuilder->where('u.name like :name')->setParameter('name', '%'.$name.'%');
        $queryBuilderCount->where('u.name like :name') ->setParameter('name','%'.$name.'%');
        
      
    }
    if($priceMin)
    {
         $queryBuilder->andWhere('u.price >= :price')->setParameter('price', $priceMin);
        $queryBuilderCount->andWhere('u.price >= :price') ->setParameter('price',$priceMin);
    }
    if($priceMax)
    {
         $queryBuilder->andWhere('u.price <= :priceMax')->setParameter('priceMax', $priceMax);
        $queryBuilderCount->andWhere('u.price <= :priceMax') ->setParameter('priceMax',$priceMax);
    }
    
    $queryBuilder->setFirstResult($offset)->setMaxResults(5);
    $queryBuilder->orderBy('u.name', 'ASC');
  
     
    $query = $queryBuilder->getQuery();
       
     
    $queryCount = $queryBuilderCount->getQuery();
    
    
     $products=$query->getArrayResult();
     $count=$queryCount->getSingleScalarResult();   
    
    
     $params= array('title' => 'Catalogue','products'=>$products, 'crtOffset'=>$offset, 'count'=>$count,'nom'=>$name,'prixMin'=>$priceMin,'prixMax'=>$priceMax);
    $template= $twig -> loadTemplate('products.twig');
    
    return $response->write($template->render($params));
}

?>