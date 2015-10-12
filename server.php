<?php 
header ('Content-type: text/html; charset=UTF-8');

$host = 'localhost';
$db = 'scmjogo';
$user = 'root';
$passwd = '';

$conn = new mysqli($host,$user,$passwd,$db);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
if (!$conn->set_charset("utf8")) {
    printf("Error loading character set utf8: %s\n", $conn->error);
} 
/*else {
    printf("Current character set: %s\n", $conn->character_set_name());
} */

if(isset($_POST['method'])){
	if(strcmp('inicia-jogo', $_POST['method']) == 0){
		//List($nome,$nRodadas,$j1,$j2) = explode(";",$_POST["data"]);
		$now = date("D M j G:i:s T Y");
		$nome = md5($now);
		$nRodadas = '10';
		$j1 = $_POST["data"];

		$arrayResponse = array();

		$sql = "select *,
				IF(id_jogador_2 IS NULL or id_jogador_2 = '', 'empty', id_jogador_2) as jogador_2
				from jogo
				limit 1;";
		$result = $conn->query($sql);
		if ($result->num_rows > 0) {
			$idJogoEncontrado;
			$nRodadas;
			$nome;
			foreach($result as $model){
				$idJogoEncontrado = $model["id"];
				$nRodadas = $model["rodadas_restantes"];
				$nome = $model["nome"];
			}
			$sql = null;
			$sql = "UPDATE jogo SET id_jogador_2 = $j1 WHERE id=$idJogoEncontrado";
			if ($conn->query($sql) === TRUE) {
				array_push($arrayResponse, array('nome_jogo'=>$nome,
												 'id_jogo'=>$idJogoEncontrado,
											     'id_j1'=>$j1,
											     'rodadas_restantes'=>$nRodadas));
				echo json_encode($arrayResponse);
			}else{
				array_push($arrayResponse, array('id'=>'erro-ao-entrar-no-jogo ['.$conn->error.']'));
				echo json_encode($arrayResponse);
			}

		}else{
			$sql = "INSERT INTO jogo VALUES
				(NULL,
				'$nome',
				'$nRodadas',
				'$j1',
				NULL,
				NULL,
				NULL)";
		
			if ($conn->query($sql) === TRUE) {
				$idJogo = $conn->insert_id;

				array_push($arrayResponse, array('nome_jogo'=>$nome,
												 'id_jogo'=>$idJogo,
											     'id_j1'=>$j1,
											     'rodadas_restantes'=>$nRodadas));
				echo json_encode($arrayResponse);
			}else{
				array_push($arrayResponse, array('id'=>'erro-ao-criar-jogo ['.$conn->error.']'));
				echo json_encode($arrayResponse);
			}

		}
		
		
		
		
	}
	else{
		$arrayResponse = array();
		array_push($arrayResponse, array('id'=>'acao-desconhecida'));
		echo json_encode($arrayResponse);
	}

}else{
	/*
	ERRO GERAL
	*/
	$arrayResponse = array();
	array_push($arrayResponse, array('id'=>'erro-geral'));
	echo json_encode($arrayResponse);
 }