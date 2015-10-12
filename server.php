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
		
		$now = date("D M j G:i:s T Y");
		$nome = md5($now);
		$nRodadas = '10';
		$j1 = $_POST["data"];

		$arrayResponse = array();

		$sql = 'select * from jogo
				where id_jogador_2 IS NULL
				limit 1;';

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
											     'rodadas_restantes'=>$nRodadas,
											     'slot'=>'2'));
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
											     'rodadas_restantes'=>$nRodadas,
											     'slot'=>'1'));
				echo json_encode($arrayResponse);
			}else{
				array_push($arrayResponse, array('id'=>'erro-ao-criar-jogo ['.$conn->error.']'));
				echo json_encode($arrayResponse);
			}
		}		
	}
	else if(strcmp('verifica-rodada', $_POST['method']) == 0){
		$arrayResponse = array();

		list($idJogo,$slot,$idRodada) = explode(";",$_POST['data']);

		$sql = "select *,rodada.id as id_rodada from rodada join jogo on (jogo.id = rodada.id_jogo)
				where jogo.id = $idJogo and (rodada.jogada_jogador_1 is null or rodada.jogada_jogador_2 is null)";

		$result = $conn->query($sql);

		if ($result->num_rows > 0) {
			$idRodada;
			foreach($result as $model){
				$idRodada = $model["id_rodada"];
			}
			$sql = null;
			$result = null;
			$sql = "select count(*) as nRodada, sum(pontuacao_jogador_1) pt_j1, sum(pontuacao_jogador_2) pt_j2 from rodada join jogo on (jogo.id = rodada.id_jogo)
					where jogo.id = $idJogo;";
			$result = $conn->query($sql);		
			if ($result->num_rows > 0) {
				$nRodada;
				$pt_j1;
				$pt_j2;
				foreach($result as $model){
					$nRodada = $model["nRodada"];
					$pt_j1 = $model["pt_j1"];
					$pt_j2 = $model["pt_j2"];
				}
				array_push($arrayResponse, array('id_rodada'=>$idRodada,
													 'n_rodada'=>$nRodada,
													 'pt_slot_1'=>$pt_j1,
													 'pt_slot_2'=>$pt_j2));
				echo json_encode($arrayResponse);
			}else{
				array_push($arrayResponse, array('id'=>"erro-ao-contar-rodadas [".$conn->error."]"));
				echo json_encode($arrayResponse);
			}
			
		}else{
			if($idRodada!="0"){
				$sql = "select * from rodada where id=$idRodada";
				$result = $conn->query($sql);		
				if ($result->num_rows > 0) {
					$jogada_j1;
					$jogada_j2;
					$pt_j1;
					$pt_j2;
					foreach($result as $model){
						$jogada_j1 = $model["jogada_jogador_1"];
						$jogada_j2 = $model["jogada_jogador_2"];
					}

					if(($jogada_j1=="0")&&($jogada_j2=="0")){
						$pt_j1=1;
						$pt_j2=1;
					}else if(($jogada_j1=="1")&&($jogada_j2=="0")){
						$pt_j1=-2;
						$pt_j2=2;
					}else if(($jogada_j1=="0")&&($jogada_j2=="1")){
						$pt_j1=2;
						$pt_j2=-2;
					}else if(($jogada_j1=="1")&&($jogada_j2=="1")){
						$pt_j1=-1;
						$pt_j2=-1;
					}
				}
				$sql = "update rodada set pontuacao_jogador_1 = $pt_j1, pontuacao_jogador_2 = $pt_j2
						where id=$idRodada";
				if ($conn->query($sql) === TRUE) {
					//salva s pontos
				}
			}
			$sql = "insert into rodada values (NULL,$idJogo,NULL,NULL,0,0,NULL)";
			if ($conn->query($sql) === TRUE) {
				$idRodada = $conn->insert_id;

				$sql = null;
				$result = null;
				$sql = "select count(*) as nRodada, sum(pontuacao_jogador_1) pt_j1, sum(pontuacao_jogador_2) pt_j2  from rodada join jogo on (jogo.id = rodada.id_jogo)
						where jogo.id = $idJogo;";
				$result = $conn->query($sql);		
				if ($result->num_rows > 0) {
					$nRodada;
					$pt_j1;
					$pt_j2;
					foreach($result as $model){
						$nRodada = $model["nRodada"];
						$pt_j1 = $model["pt_j1"];
						$pt_j2 = $model["pt_j2"];
					}
					array_push($arrayResponse, array('id_rodada'=>$idRodada,
													 'n_rodada'=>$nRodada,
													 'pt_slot_1'=>$pt_j1,
													 'pt_slot_2'=>$pt_j2));
					echo json_encode($arrayResponse);
				}else{
					array_push($arrayResponse, array('id'=>"erro-ao-contar-rodadas [".$conn->error."]"));
					echo json_encode($arrayResponse);
				}

			}else{
				array_push($arrayResponse, array('id'=>"erro-ao-criar-rodada [".$conn->error."]"));
				echo json_encode($arrayResponse);
			}
		}

	}
	else if(strcmp('jogar', $_POST['method']) == 0){
		$arrayResponse = array();

		list($jogada,$idJogo,$idRodada,$slot) = explode(";",$_POST['data']);

		$sql = null;
		
		if($slot=="1"){
			$sql = "update rodada set jogada_jogador_1 = $jogada where id=$idRodada";
		}else if ($slot=="2"){
			$sql = "update rodada set jogada_jogador_2 = $jogada where id=$idRodada";
		}

		if ($conn->query($sql) === TRUE) {
			array_push($arrayResponse, array('id'=>"jodada-realizada"));
			echo json_encode($arrayResponse);
		}else{
			array_push($arrayResponse, array('id'=>"erro-ao-jogar [".$conn->error."]"));
			echo json_encode($arrayResponse);
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