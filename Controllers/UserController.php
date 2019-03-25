<?php


use Monolog\Logger;
use Monolog\Handler\StreamHandler;

// create a log channel
$log = new Logger('name');
$log->pushHandler(new StreamHandler(__DIR__ .'/user.log', Logger::WARNING));

$app->get('/register',form)->setName('register');

function form($request,$response, $args){
     global $twig;
     
    $params= array('title' => 'Inscription');
    $template= $twig -> loadTemplate('form.twig');
    
    return $response->write($template->render($params));
}



$app->get('/login',login)->setName('login');

function login($request,$response, $args){
     global $twig;
     
    $params= array('title' => 'Connexion');
    $template= $twig -> loadTemplate('login.twig');
    
    return $response->write($template->render($params));
}

$app->get('/logout',logout)->setName('logout');

function logout($request,$response, $args){
     global $twig;
     
     session_unset();
    session_destroy();
   
      $response = $response->withRedirect('/');
      
      return $response;
}

$app->post('/validate',validate)->setName('validate');

function validate($request,$response, $args){
     global $twig;
     global $entityManager;
     
   
     $allPostPutVars = $request->getParsedBody();
     $name;
     $lastname;
     $mail;
     $userName;
     $street;
     $streetNumber;
     $City;
     $PostalCode;
     $errorMessage='';
     $password;
     
     $hasError=false;
     //check
     
    if (empty($allPostPutVars['nom']) || preg_match('/[A-Za-z]*/', $allPostPutVars['nom'])==0 ) {
               $errorMessage.= 'Nom de famille vide ou invalide ';
               $hasError=true;
        } else {
             $lastname=$allPostPutVars['nom'];
        }
     
    if (empty($allPostPutVars['prenom']) || preg_match('/[a-zA-Z]*-?[a-zA-Z]*/', $allPostPutVars['prenom'])==0 ) {
               $errorMessage.= 'Prenom vide ou invalide ';
               $hasError=true;
        } else {
             $name=$allPostPutVars['prenom'];
        }
        
     if (empty($allPostPutVars['email']) || filter_var($allPostPutVars['email'], FILTER_VALIDATE_EMAIL)==false ) {
               $errorMessage.= 'Email vide ou invalide ';
               $hasError=true;
        } else {
             $mail=$allPostPutVars['email'];
        }
        
   if (empty($allPostPutVars['login']) || preg_match('/[A-Za-z0-9]*/', $allPostPutVars['login'])==0 ) {
               $errorMessage.= 'Nom d\'utilisateur vide ou invalide ';
               $hasError=true;
        } else {
             $userRepository = $entityManager->getRepository('User');
             $user = $userRepository->findOneBy(array('username' =>
                                              $allPostPutVars['login']));
            //check if username alreay exists
            if($user)
            {
                 $errorMessage.= 'Nom d\'utilisateur déjà utilisé ';
               $hasError=true;
            }
            else
            {
                $userName=$allPostPutVars['login'];
        
            }
            }
        
    if (empty($allPostPutVars['mdp']) || preg_match('/(?=^.{8,}$)((?=.*\d)|(?=.*\W+))(?![.\n])(?=.*[A-Z])(?=.*[a-z]).*$/', $allPostPutVars['mdp'])==0 ) {
               $errorMessage.= 'Mot de passe vide ou invalide ';
               $hasError=true;
        } else {
             if (empty($allPostPutVars['mdpCheck']) || preg_match('/(?=^.{8,}$)((?=.*\d)|(?=.*\W+))(?![.\n])(?=.*[A-Z])(?=.*[a-z]).*$/', $allPostPutVars['mdpCheck'])==0 || $allPostPutVars['mdp'] !=$allPostPutVars['mdpCheck']) {
               $errorMessage.= 'Les mots de passes ne correspondent pas ';
               $hasError=true;
                } else
                {
                     $password=$allPostPutVars['mdp'];
                }
        }
       
    if (empty($allPostPutVars['nomRue']) || preg_match('/([A-z0-9À-ž\s]){2,}/', $allPostPutVars['nomRue']) ==0) {
               $errorMessage.= 'Nom de rue vide ou invalide ';
               $hasError=true;
        } else {
             $street=$allPostPutVars['nomRue'];
        }
        
    if (empty($allPostPutVars['numeroRue']) || !is_numeric($allPostPutVars['numeroRue']) ) {
               $errorMessage.= 'Numéro de rue vide ou invalide ';
               $hasError=true;
        } else {
             $streetNumber=$allPostPutVars['numeroRue'];
        }
        
     if (empty($allPostPutVars['ville']) || preg_match('/[a-zA-Z]*-?[a-zA-Z]*/', $allPostPutVars['ville'])==0 ) {
               $errorMessage.= 'Nom de ville vide ou invalide ';
               $hasError=true;
        } else {
             $City=$allPostPutVars['ville'];
        }
        
    if (empty($allPostPutVars['codeP']) || preg_match('/[0-9]{5}/', $allPostPutVars['codeP']) ==0) {
               $errorMessage.= 'Code postal vide ou invalide ';
               $hasError=true;
        } else {
             $PostalCode=$allPostPutVars['codeP'];
        }
     //if error, redirect to form with previous value and error message
     if($hasError){
        $params= array('title' => 'Inscription','lastname'=>$lastname,'name'=>$name,'email'=>$mail,'userName'=>$userName,'street'=>$street,'streetnumber'=>$streetNumber,'City'=>$City,'postalCode'=>$PostalCode,"errorMsg"=>$errorMessage );
        $template= $twig -> loadTemplate('form.twig');
    
    return $response->write($template->render($params));
     }
     
     //otherwise carry on
     
     
     $user = new User;
     
    $user->setLastname($lastname);
    $user->setName($name);
    $user->setEmail($mail);
    $user->setUsername($userName);
    $user->setPassword(password_hash($password, PASSWORD_DEFAULT));
    $user->setStreet($street);
    $user->setStreetnumber($streetNumber);
    $user->setCity($City);
    $user->setPostalcode($PostalCode);
      $user->setRole(1);
    $entityManager->persist($user);
    $entityManager->flush();
    
    $params= array('title' => 'Connexion','successMsg'=>'Inscription réussie!');
    $template= $twig -> loadTemplate('login.twig');
    
    return $response->write($template->render($params));
}


$app->post('/checkLogin',checkLogin)->setName('checkLogin');

function checkLogin($request,$response, $args){
     global $twig;
     global $entityManager;
     global $app;
     global $log;
   
     $allPostPutVars = $request->getParsedBody();
     
     $userRepository = $entityManager->getRepository('User');

     $user = $userRepository->findOneBy(array('username' =>
                                              $allPostPutVars['login']));
    if($user)
    {
        
        if(password_verify( $allPostPutVars['mdp'] , $user->getPassword() ) )
       {
           	$_SESSION['user_id'] = $user->getUsername();
           	
           	//check for admin priviliges
           	$log->warning($user->getRole()->getId());
           	if($user->getRole() && $user->getRole()->getId() ==2)
           	{
           	    $_SESSION['user_admin'] = true;
           	}
           	
            $params= array('title' => 'Validation', 'name' =>$allPostPutVars['nom']);
            $template= $twig -> loadTemplate('validation.twig');
        
             return $response->write($template->render($params));
       }
    }
    
     $params= array('title' => 'Connexion','errorMsg'=>'Nom d\'utilisateur ou mot de passe incorrect');
    $template= $twig -> loadTemplate('login.twig');
    
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



$app->get('/account',accountInformation)->setName('account');

function accountInformation($request,$response, $args){

    global $twig;
    global $entityManager;
    global $app;
    global $log;
   
    
     
    $userRepository = $entityManager->getRepository('User');
    
    
    $log->warning('I just got the logger');
    $user = $userRepository->findOneBy(array('username' =>$_SESSION['user_id']));
    
    
    $params= array('title' => 'Mon Compte','lastname'=>$user->getLastname(),'name'=>$user->getName(),'email'=>$user->getEmail(),'username'=>$user->getUsername(),'street'=>$user->getStreet(),'streetnumber'=>$user->getStreetNumber(),'City'=>$user->getCity(),'PostalCode'=>$user->getPostalCode() ,'successMsg'=>TempData::get("successMsg"), 'errorMsg' =>TempData::get("errorMsg"));
      
    $template= $twig -> loadTemplate('accountInformation.twig');
    
    return $response->write($template->render($params));
}


$app->post('/updateAdress',updateAdress)->setName('updateAdrress');

function updateAdress($request,$response, $args){
     global $twig;
     global $entityManager;
     global $log;
   
     $allPostPutVars = $request->getParsedBody();
     $userRepository = $entityManager->getRepository('User');
    
      $user = $userRepository->findOneBy(array('username' =>$_SESSION['user_id']));
    
    
    
     $userName;
     $street;
     $streetNumber;
     $City;
     $PostalCode;
     $errorMessage='';
   
     $hasError=false;
     //check
     
    
    if (empty($allPostPutVars['nomRue']) || preg_match('/([A-z0-9À-ž\s]){2,}/', $allPostPutVars['nomRue']) ==0) {
               $errorMessage.= 'Nom de rue vide ou invalide ';
               $hasError=true;
        } else {
             $street=$allPostPutVars['nomRue'];
        }
        
    if (empty($allPostPutVars['numeroRue']) || !is_numeric($allPostPutVars['numeroRue']) ) {
               $errorMessage.= 'Numéro de rue vide ou invalide ';
               $hasError=true;
        } else {
             $streetNumber=$allPostPutVars['numeroRue'];
        }
        
     if (empty($allPostPutVars['ville']) || preg_match('/[a-zA-Z]*-?[a-zA-Z]*/', $allPostPutVars['ville']) ==0) {
               $errorMessage.= 'Nom de ville vide ou invalide ';
               $hasError=true;
        } else {
             $City=$allPostPutVars['ville'];
        }
        
    if (empty($allPostPutVars['codeP']) || preg_match('/[0-9]{5}/', $allPostPutVars['codeP'])==0 ) {
               $errorMessage.= 'Code postal vide ou invalide ';
               $hasError=true;
        } else {
             $PostalCode=$allPostPutVars['codeP'];
        }
         $log->warning($allPostPutVars['codeP']);
     //if error, redirect to form with previous value and error message
     if($hasError){
           $log->warning('Error updating adress');
           
             TempData::set("errorMsg",  $errorMessage);
         
        $response = $response->withRedirect('/account');
      
     return $response;
         
  
     }
     
     //otherwise carry on
     
     
    
    $user->setStreet($street);
    $user->setStreetnumber($streetNumber);
    $user->setCity($City);
    $user->setPostalcode($PostalCode);
    
    $entityManager->persist($user);
    $entityManager->flush();
      TempData::set("successMsg", "Change saved");
    $response = $response->withRedirect('/account');
      
     return $response;
}

$app->post('/updatePassword',updatePassword)->setName('updatePassword');

function updatePassword($request,$response, $args){
     global $twig;
     global $entityManager;
     global $log;
   
     $allPostPutVars = $request->getParsedBody();
     $userRepository = $entityManager->getRepository('User');
    
      $user = $userRepository->findOneBy(array('username' =>$_SESSION['user_id']));
    
    
    
     $userName;
    $password;
     $errorMessage='';
   
     $hasError=false;
     //check
         
    if (empty($allPostPutVars['mdp']) || preg_match('/(?=^.{8,}$)((?=.*\d)|(?=.*\W+))(?![.\n])(?=.*[A-Z])(?=.*[a-z]).*$/', $allPostPutVars['mdp'])==0 ) {
               $errorMessage.= 'Mot de passe vide ou invalide ';
               $hasError=true;
        } else {
             if (empty($allPostPutVars['mdpCheck']) || preg_match('/(?=^.{8,}$)((?=.*\d)|(?=.*\W+))(?![.\n])(?=.*[A-Z])(?=.*[a-z]).*$/', $allPostPutVars['mdpCheck'])==0 || $allPostPutVars['mdp'] !=$allPostPutVars['mdpCheck']) {
               $errorMessage.= 'Les mots de passes ne correspondent pas ';
               $hasError=true;
                } else
                {
                     $password=$allPostPutVars['mdp'];
                }
        }
    
   
     //if error, redirect to form with previous value and error message
     if($hasError){
           $log->warning('Error updating password');
         
            TempData::set("errorMsg", $errorMessage);
         
        $response = $response->withRedirect('/account');
         
  
     }
     
     //otherwise carry on
     
     
     $user->setPassword(password_hash($password, PASSWORD_DEFAULT));
    
    $entityManager->persist($user);
    $entityManager->flush();
    TempData::set("successMsg", "Change saved");
    
    $response = $response->withRedirect('/account');
      
     return $response;
}



?>