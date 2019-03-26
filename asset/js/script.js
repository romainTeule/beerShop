
    

	function passWordCheck()
	{
		var password =   document.getElementById("mdp").value;
		var check = document.getElementById("mdpCheck").value;

		return password==check;
	}


  	

  	document.getElementById("formulaire").addEventListener("submit", function(e){
	    if(!passWordCheck())
	    {
			
			$("#modalPassword").modal('show');
				//alert("Mauvais mot de passe...");
				e.preventDefault();
	    }
	});
	
