<?php
	/*  --------------------------------
	 *  We use the session on this page
	 *  --------------------------------*/
    if (session_status() == PHP_SESSION_NONE){
        session_start();
    }// end if

    if (!isset($_SESSION["security-level"])){
        $_SESSION["security-level"] = 0;
    }// end if

	/* ----------------------------------------
	 *	initialize security level to "insecure"
	 * ----------------------------------------*/
	if (!isset($_SESSION['security-level'])){
		$_SESSION['security-level'] = '0';
	}// end if

	/* ------------------------------------------
	 * Constants used in application
	 * ------------------------------------------ */
	require_once('../../includes/constants.php');
	require_once('../../includes/minimum-class-definitions.php');

	function populatePOSTSuperGlobal(){
		$lParameters = Array();
		parse_str(file_get_contents('php://input'), $lParameters);
		$_POST = $lParameters + $_POST;
	}// end function populatePOSTArray

	function getPOSTParameter($pParameter, $lRequired){
		if(isset($_POST[$pParameter])){
			return $_POST[$pParameter];
		}else{
			if($lRequired){
				throw new Exception("POST parameter ".$pParameter." is required");
			}else{
				return "";
			}
		}// end if isset
	}// end function validatePOSTParameter

	function jsonEncodeQueryResults($pQueryResult){
		$lDataRows = array();
		while ($lDataRow = mysqli_fetch_assoc($pQueryResult)) {
			$lDataRows[] = $lDataRow;
		}// end while

		return json_encode($lDataRows);
	}//end function jsonEncodeQueryResults

	try{
		$lAccountUsername = "";
		$lVerb = $_SERVER['REQUEST_METHOD'];

		switch($lVerb){
			case "GET":
				if(isset($_GET['username'])){
					/* Example hack: username=jeremy'+union+select+concat('The+password+for+',username,'+is+',+password),mysignature+from+accounts+--+ */
					$lAccountUsername = $_GET['username'];

					if ($lAccountUsername == "*"){
						/* List all accounts */
						$lQueryResult = $SQLQueryHandler->getUsernames();
					}else{
						/* lookup user */
						$lQueryResult = $SQLQueryHandler->getNonSensitiveAccountInformation($lAccountUsername);
					}// end if

					if ($lQueryResult->num_rows > 0){
						echo "Result: {Accounts: {".jsonEncodeQueryResults($lQueryResult)."}}";
					}else{
						echo "Result: {User '".$lAccountUsername."' does not exist}";
					}// end if

				}else{

					/* Display help and list accounts */
					echo
						"
						<head>
						
						</head>
						<style>
							:root{
								--color-v: #548235;
								--color-f: #414141;
								--color-y: #FFE699;
								--color-a1: #BDD7EE;
								--color-a2: #ADB9CA;

							}				
						
							.poli-section{
								display: flex !important;
								flex-direction: row !important;
								flex-wrap: nowrap !important;
								justify-content: center  !important;
								max-width: 50%;
								margin:5px !important;
								min-height: 50px !important;
								font-size: small;							
							}

							p{
								padding: 10px; !important;
							}
							
							.poli-section div{
								flex-grow: 1 !important;

							}
							.poli-section span{
								font-family: Verdana, Geneva, Tahoma, sans-serif !important;
								font-weight:bold !important;
							}
							
							.yellow{
								background-color: var(--color-y) !important
							}
							.blue1{
								background-color: var(--color-a1) !important
							}
							.blue2{
								background-color: var(--color-a2) !important
							}
							.white{
									color: white;
							}

						</style>
					<body>	
						
						
						<a href='//".$_SERVER['HTTP_HOST']."/index.php' style='cursor:pointer;text-decoration:none;font-weight:bold;'/>Back to Home Page</a>

						
						<div class='poli-section '>
							<div class='yellow'>
								<p>
									<span>Ayuda:</span>
									Este servicio expone los metodos GET, POST, PUT, DELETE. Este servicio es vulneralbe a  Inyecciones SQL en seguridad level 0.
								</p>
							</div>

							<div class='blue1'>
								<p>
									<span>DEFAULT GET:</span> 
									(Sin ningun parametro) Mostrará esta ayuda extra y un listado de cuentas del sistema.
								</p>
								<p><span>Parametro opcional</span>: None.</p>

							</div>					
						</div>

						<div class='poli-section blue2'>
							<div>
								<p>
									<span>GET:</span>
								 	Muestra el nombre de usuario de todas las cuentas  o el nombre de usuario y la firma de una cuenta.
								</p>
								<p>
									<span>Parametros opcionales</span>:
									Nombre de usuario como parametro URL. Si el nombre de usuario es &quot;*&quot;  Se devolveran todas las cuentas.
								</p>
								
								<p>
									<span>Example(s):</span>
								</p>
								<p>
								
									 Un usuario en particular:
									  <a href='".$_SERVER['HTTP_HOST']."/webservices/rest/ws-user-account.php?username=adrian'>/rest/ws-user-account.php?username=adrian</a>
								</p>
								<p>
									Obtener todos los usuarios: 
									<a href='//".$_SERVER['HTTP_HOST']."/webservices/rest/ws-user-account.php?username=*'>/webservices/rest/ws-user-account.php?username=*</a>
								</p>
								<p>
									<span>Example Exploit(s):</span>
								</p>
								<p>
									SQL injection:
									<a href='".$_SERVER['HTTP_HOST']."/webservices/rest/ws-user-account.php?username=%6a%65%72%65%6d%79%27%20%75%6e%69%6f%6e%20%73%65%6c%65%63%74%20%63%6f%6e%63%61%74%28%27%54%68%65%20%70%61%73%73%77%6f%72%64%20%66%6f%72%20%27%2c%75%73%65%72%6e%61%6d%65%2c%27%20%69%73%20%27%2c%20%70%61%73%73%77%6f%72%64%29%2c%6d%79%73%69%67%6e%61%74%75%72%65%20%66%72%6f%6d%20%61%63%63%6f%75%6e%74%73%20%2d%2d%20'>/webservices/rest/ws-user-account.php?username=jeremy'+union+select+concat('The+password+for+',username,'+is+',+password),mysignature+from+accounts+--+
									</a>
								</p>

							</div>
						</div>
						<div class='poli-section' >
											
							<div class='blue1'>
								<p>
									<span>POST:</span> 
									Creates new account.
								<p>
								<p>
									<span>Parametros requeridos</span>: 
									username, password AS POST parameter.
								<p>
								<p>
									<span>Optional params</span>
									: signature AS POST parameter.
								<p>
							</div>
						
					
							<div class='blue2 white'>
								<p>
									<span>PUT:</span>
									Creates or updates account.
								</p>
								<p>
									<span>
									Required params</span>: username, password AS POST parameter.
								</p>
								<p>
									<span>Optional params</span>
									: signature AS POST parameter.
								<%p>
							</div>
					
						</div>

						<div class='poli-section blue2' >
							<span>DELETE:</span> Deletes account.
							<span>Required params</span>: username, password AS POST parameter.
							<span>Optional params</span>: None.
						</div>
					<body>	
				";

						
				



						
					

					

					
					
				}// end if

			break;
			case "POST"://create

				$lAccountUsername = getPOSTParameter("username", TRUE);
				$lAccountPassword = getPOSTParameter("password", TRUE);
				$lAccountSignature = getPOSTParameter("signature", FALSE);

				if ($SQLQueryHandler->accountExists($lAccountUsername)){
					echo "Result: {Account ".$lAccountUsername." already exists}";
				}else{
					$lQueryResult = $SQLQueryHandler->insertNewUserAccount($lAccountUsername, $lAccountPassword, $lAccountSignature);
					echo "Result: {Inserted account ".$lAccountUsername."}";
				}// end if

			break;
			case "PUT":	//create or update
				/* $_POST array is not auto-populated for PUT method. Parse input into an array. */
				populatePOSTSuperGlobal();

				$lAccountUsername = getPOSTParameter("username", TRUE);
				$lAccountPassword = getPOSTParameter("password", TRUE);
				$lAccountSignature = getPOSTParameter("signature", FALSE);

				if ($SQLQueryHandler->accountExists($lAccountUsername)){
					$lQueryResult = $SQLQueryHandler->updateUserAccount($lAccountUsername, $lAccountPassword, $lAccountSignature);
					echo "Result: {Updated account ".$lAccountUsername.". ".$lQueryResult." rows affected.}";
				}else{
					$lQueryResult = $SQLQueryHandler->insertNewUserAccount($lAccountUsername, $lAccountPassword, $lAccountSignature);
					echo "Result: {Inserted account ".$lAccountUsername.". ".$lQueryResult." rows affected.}";
				}// end if

			break;
			case "DELETE":
				/* $_POST array is not auto-populated for DELETE method. Parse input into an array. */
				populatePOSTSuperGlobal();

				$lAccountUsername = getPOSTParameter("username", TRUE);
				$lAccountPassword = getPOSTParameter("password", TRUE);

				if($SQLQueryHandler->accountExists($lAccountUsername)){

					if($SQLQueryHandler->authenticateAccount($lAccountUsername,$lAccountPassword)){
						$lQueryResult = $SQLQueryHandler->deleteUser($lAccountUsername);

						if ($lQueryResult){
							echo "Result: {Deleted account ".$lAccountUsername."}";
						}else{
							echo "Result: {Attempted to delete account ".$lAccountUsername." but result returned was ".$lQueryResult."}";
						}//end if

					}else{
						echo "Result: {Could not authenticate account ".$lAccountUsername.". Password incorrect.}";
					}// end if

				}else{
					echo "Result: {User '".$lAccountUsername."' does not exist}";
				}// end if

			break;
			default:
				throw new Exception("Could not understand HTTP REQUEST_METHOD verb");
			break;
		}// end switch

	} catch (Exception $e) {
		echo $CustomErrorHandler->FormatErrorJSON($e, "Unable to process request to web service ws-user-account");
	}// end try

?>
