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
       global $step;
     $products;
     $offset;
     
     
     if($args['offset'])
     {
        $offset=   $args['offset'];
     }
     else if($request->getParam('offset'))
     {
         $offset=$request->getParam('offset');
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
    
    if($offset>=$count)
    {
        $offset=0;
    }
        
     $products=$productRepository->findBy(array(),array('name' => 'ASC'), $step, $offset);


    $params= array('title' => 'Gestion catalogue','products'=>$products, 'crtOffset'=>$offset, 'count'=>$count,'successMsg'=>TempData::get("successMsg"), 'errorMsg' =>TempData::get("errorMsg"));
    $template= $twig -> loadTemplate('manageProducts.twig');
    
    return $response->write($template->render($params));
}


$app->post('/updateProduct/{offset}[/{id}]',updateProduct)->setName('updateProduct');



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
     
      if($args['offset'])
     {
        $offset=$args['offset'];
     }
     else
     {
       $offset=0;
     
     }
       
       
    
   $allPostPutVars = $request->getParsedBody();
       
       
    $product->setName($allPostPutVars[nom]);
    $product->setDescription($allPostPutVars[description]);
    $product->setPrice($allPostPutVars[prix]);
    
    if(!$allPostPutVars[url])
    {
        $img='https://www.tampabay.com/storyimage/HI/20190227/ARTICLE/190229705/AR/0/AR-190229705.jpg&MaxW=1200&Q=66';
    }
    else
    {
        $img=$allPostPutVars[url];
    }
     $product->setImg($img);
        
    $entityManager->persist($product);
    $entityManager->flush();
         
     TempData::set("successMsg", "Change saved");
         
    $response = $response->withRedirect($app->getContainer()->get('router')->pathFor('manageProducts', [], [
    'offset' =>$offset,
    ]));
      
    return $response;
}

$app->get('/deleteProduct/{offset}/{id}',deleteProduct)->setName('deleteProduct');



function deleteProduct($request,$response, $args){
   global $twig;
   global $entityManager;
   global $app;
   
   
    $productRepository = $entityManager->getRepository('Product');
     
    $product=$productRepository->findOneBy(array('id' =>
                                              $args['id']));
    
       
    
   
    $entityManager->remove($product);
    $entityManager->flush();
    
    if($args['offset'])
     {
        $offset=$args['offset'];
     }
     else
     {
       $offset=0;
     
     }
     
     TempData::set("successMsg", "Product deleted");
         
    $response = $response->withRedirect($app->getContainer()->get('router')->pathFor('manageProducts', [], [
    'offset' => $offset,
    ]));
      
    return $response;
}


$app->get('/naviguateCatalogue[/{offset}]',naviguateCatalogue)->setName('naviguateCatalogue');


function naviguateCatalogue($request,$response, $args){
     global $twig;
     global $entityManager;
     global $step;
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
    
    
    $queryCount = $queryBuilderCount->getQuery();
    
    
     
     $count=$queryCount->getSingleScalarResult();   
     if($offset>=$count)
    {
        $offset=0;
    }
    
    $queryBuilder->setFirstResult($offset)->setMaxResults($step);
    $queryBuilder->orderBy('u.name', 'ASC');
     $query = $queryBuilder->getQuery();
    $products=$query->getArrayResult();
    
     $params= array('title' => 'Catalogue','products'=>$products, 'crtOffset'=>$offset, 'count'=>$count,'nom'=>$name,'prixMin'=>$priceMin,'prixMax'=>$priceMax);
    $template= $twig -> loadTemplate('products.twig');
    
    return $response->write($template->render($params));
}

?>