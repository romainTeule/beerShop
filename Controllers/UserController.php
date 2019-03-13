<?php


$app->get('/form',form);

function form($request,$response, $args){
     global $twig;
     
    $params= array('title' => 'Formulaire');
    $template= $twig -> loadTemplate('form.twig');
    
    return $response->write($template->render($params));
}

$app->post('/validate',validate);

function validate($request,$response, $args){
     global $twig;
     global $entityManager;
     
   
     $allPostPutVars = $request->getParsedBody();
     
     
     
     $user = new User;
     
     $user->setLastname($allPostPutVars['nom']);
     $user->setName($allPostPutVars['prenom']);
    $user->setEmail($allPostPutVars['email']);
    $user->setUsername($allPostPutVars['login']);
    $user->setPassword(password_hash($allPostPutVars['mdp'], PASSWORD_DEFAULT));
    $user->setStreet($allPostPutVars['nomRue']);
    $user->setStreetnumber($allPostPutVars['numeroRue']);
    $user->setCity($allPostPutVars['ville']);
    $user->setPostalcode($allPostPutVars['codeP']);
    
    $entityManager->persist($user);
    $entityManager->flush();
    
    $params= array('title' => 'Validation', 'name' =>$allPostPutVars['nom']);
    $template= $twig -> loadTemplate('validation.twig');
    
    return $response->write($template->render($params));
}

$app->get('/user/{id}', function ($request, $response, $args) {
    global $twig;
     global $entityManager;
     
    
     
     $userRepository = $entityManager->getRepository('User');

    $user = $userRepository->findOneBy(array('id' =>
     $args['id']));
     
      $params= array('title' => 'User Presentation', 'name' =>$user->getName(),"surname"=>$user->getSurname());
    $template= $twig -> loadTemplate('UserPage.twig');
    
    return $response->write($template->render($params));
});
?>