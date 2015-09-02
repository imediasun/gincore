<?php
//==================================
//Connect to data base
//==================================
function connect(){

	$conn=new PDO("mysql:host=localhost;dbname=admin_restore",'admin_restore','H@tlin3r1');
	$conn->query("SET NAMES utf8;");
	header('Content-Type: text/html; charset=utf-8');
	return $conn;
}
//==================================
//Begin config
//==================================
function get_config($cath)
{
	
	if ($cath=='mainHeaders') {
		$arr=array(141,6,142,143,144,145,146,147,148,10,486,444,449);

			$newtitle = 'у Львові в Re:Store';
			$description1 = 'за найнижчими цінами у Львові. Безкоштовна діагностика — сервісний центр Re:Store';
			$description2='';
			return array(
				'arr'=>$arr,
				'newtitle'=>$newtitle,
				'description1'=>$description1,
				'description2'=>$description2,
				);
	}
	if ($cath=='others') {
		$arr=array(13,149,910,912,914,65,66);

			$newtitle = '';
			$description1 = '';
			$description2='';
			return array(
				'arr'=>$arr,
				'newtitle'=>$newtitle,
				'description1'=>$description1,
				'description2'=>$description2,
				);
	}
	if ($cath=='special') {
		$arr=array(150,1,11,152,4,151);

			$newtitle = '';
			$description1 = '';
			$description2='';
			return array(
				'arr'=>$arr,
				'newtitle'=>$newtitle,
				'description1'=>$description1,
				'description2'=>$description2,
				);
	}
	if ($cath=='mobile') 
	{
		$arr=array(6,8,7,5,18,72,74,123,359,75,363,975,360,1067,980,1102,1091,1083,979,1099,987,991,1132,992);
		$newtitle = 'у Львові: заміна скла, екрану — сервісний центр Re:Store';
		$description1 = 'за найнижчими цінами у Львові. Безкоштовна діагностика';
		$description2=', швидкий ремонт і гарантія — сервісний центр Re:Store';

		return array(
			'arr'=>$arr,
			'newtitle'=>$newtitle,
			'description1'=>$description1,
			'description2'=>$description2,
			);
	}
	if ($cath=='laptop') {
		$arr=array(10,9,329,333,332,355,330,334,1210,331,358,357,356);

			$newtitle = 'у Львові: заміна матриці, екрану , клавіатури — Re:Store';
			$description1 = 'за найнижчими цінами у Львові. Безкоштовна діагностика';
			$description2=', швидкий ремонт і гарантія — сервісний центр Re:Store';
			return array(
				'arr'=>$arr,
				'newtitle'=>$newtitle,
				'description1'=>$description1,
				'description2'=>$description2,
				);
	}
}

//==================================
//End config
//==================================
function start($conn,$cath='mobile',$command=0){


	$res=get_config($cath);
	$arr=$res['arr'];
	$newtitle=$res['newtitle'];
	$description1=$res['description1'];
	$description2=$res['description2'];


	for ($i=0 ; $i<count($arr); $i++) {
		if($cath=='mobile' || $cath=='laptop')
		{
			$stmt=$conn->prepare("
				SELECT ms.id, ms.map_id, ms.name, ms.fullname, ms.metadescription, ms.lang from restore4_map_strings as ms
				JOIN restore4_map as m
				ON m.id=ms.map_id
				WHERE m.parent=$arr[$i] AND ms.lang LIKE '%lviv%' 
				");
		}else{
			$stmt=$conn->prepare("
				SELECT ms.id, ms.map_id, ms.name, ms.fullname, ms.metadescription, ms.lang from restore4_map_strings as ms
				WHERE ms.map_id=$arr[$i] AND ms.lang LIKE '%lviv%' 
				");
		}

		$stmt->execute();
		$res=$stmt->fetchAll();
		$nres = create($res,$newtitle,$description1,$description2,$cath);
		switch ($command) {
		case 0:
				view($conn, $res, $nres);
				break;
		case 1:
				update($conn, $nres);
				break;

		}
	}
}
//==================================
//Function that created correct fullname and metadescroption
//==================================
function create($arr=array(),$newtitle,$description1,$description2,$cath){

	for ($i=0;$i<count($arr);$i++) 
	{

			$title='Ремонт '.$arr[$i]['name'];
			$arr[$i]['fullname'] = $title . ' ' . $newtitle;
			$arr[$i]['metadescription'] = $title . ' ' . $description1 . ' ' . $arr[$i]['name'] . $description2;

			if($cath=='mainHeaders'){

				$arr[$i]['fullname'] = $arr[$i]['name'] . ' ' . $newtitle;
				$arr[$i]['metadescription'] = $arr[$i]['name']. ' ' . $description1 . ' ' . $description2;
			}
			if($cath=='special'){

					if($arr[$i]['map_id']==150){
						$arr[$i]['fullname'] = 'Контакти компанії Re:Store | Ми знаходимося тут | сервісний центр Apple';
						$arr[$i]['metadescription'] = 'Сервісний центр Re:Store знаходиться за адресою. м. Львів, вул. Маєра Балабана, 6';
					}
					if($arr[$i]['map_id']==1){
						$arr[$i]['fullname'] = 'Re:Store - ремонт сенсорних телефонів, ноутбуків, мобільних, планшетів у Львові';
						$arr[$i]['metadescription'] = 'Сервіс та ремонт сенсорних телефонів , ремонт мобільних телефонів, ремонт ноутбуків, планшетів. Сервісний центр Apple у Львові. ☎ (032) 290-42-90 ★ вул. Маєра Балабана, 6 Ціни тут ►►►';
					}
					if($arr[$i]['map_id']==11){
						$arr[$i]['fullname'] = 'Відгуки про компанію Re:Store';
						$arr[$i]['metadescription'] = 'Відгуки клієнтів про компанію Re:Store. Відгуки про якість роботи нашої кампанії. Переконайся в якісній роботі!';
					}
					if($arr[$i]['map_id']==152){
						$arr[$i]['fullname'] = 'Контакти компанії Re:Store | Ми знаходимося тут | сервісний центр Apple';
						$arr[$i]['metadescription'] = 'Сервісний центр Re:Store знаходиться за адресою. вул. Маєра Балабана, 6';
					}
					if($arr[$i]['map_id']==4){
						$arr[$i]['fullname'] = 'Контакти компанії Re:Store | Ми знаходимося тут | сервісний центр Apple';
						$arr[$i]['metadescription'] = 'Сервісний центр Re:Store знаходиться за адресою. вул. Маєра Балабана, 6. Карта проїзду';
					}
					if($arr[$i]['map_id']==151){
						$arr[$i]['fullname'] = 'Наші переваги';
						$arr[$i]['metadescription'] = '';
					}
			}

			if($cath=='others'){
				$arr[$i]['fullname'] = '';
				$arr[$i]['metadescription'] = '';
			}
		}

		return $arr;
}
//==================================
//Function that update {map_strings} table
//==================================
function update($conn, $arr=array()){

		try{
			foreach ($arr as $key => $value) 
			{

				$stmt=$conn->prepare('
					UPDATE restore4_map_strings 
					SET fullname = "' . $value['fullname'] . '"
					WHERE id="' . $value['id'] . '  "
					');
				$stmt->execute();



				$stmt=$conn->prepare('
					UPDATE restore4_map_strings 
					SET metadescription = "' . $value['metadescription'] . '"
					WHERE id="' . $value['id'] . '  "
					');
				$stmt->execute();
			}
		}catch(Exception $e){
			echo $e->getMessage();
			die();
		}
}
//==================================
//Function that shows us old and new version of fullname and metadescroption
//==================================

function view($conn, $res, $arr)
{
	for ($i=0; $i<count($res); $i++):?>

			<div class="container">
			<strong>It is now:</strong>
				<div class="row thumbnail">
						<div class="col-sm-2"><text>id:</text></div>
						<div class="col-sm-6"><text><?= $res[$i]['id'];?></text></div>
				</div>
				<div class="row thumbnail">
						<div class="col-sm-2"><text>map_id:</text></div>
						<div class="col-sm-6"><text><?= $res[$i]['map_id'];?></text></div>
				</div>
				<div class="row thumbnail">
						<div class="col-sm-2"><text>name:</text></div>
						<div class="col-sm-6"><text><?= $res[$i]['name'];?></text></div>
				</div>
				<div class="row thumbnail">
						<div class="col-sm-2"><text>fullname</text></div>
						<div class="col-sm-6"><text><?= $res[$i]['fullname'];?></text></div>
				</div>
				<div class="row thumbnail">
						<div class="col-sm-2"><text>metadescription</text></div>
						<div class="col-sm-6"><text><?= $res[$i]['metadescription'];?></text></div>
				</div>

				<strong>It will be:</strong>
				<div class="row thumbnail">
						<div class="col-sm-2"><text>fullname</text></div>
						<div class="col-sm-6"><text><?= $arr[$i]['fullname'];?></text></div>
				</div>
				<div class="row thumbnail">
						<div class="col-sm-2"><text>metadescription</text></div>
						<div class="col-sm-6"><text><?= $arr[$i]['metadescription'];?></text></div>
				</div>
			</div>
			<hr>

	<?php endfor;
}

if(isset($_POST)){
	$cathegory=$_POST['cathegory'];
	$command=$_POST['command'];
	$conn=connect();
	start($conn,$_POST['cathegory'],intval($_POST['command']));
}

?>
<html>
<head>
<!-- 	<meta charset="utf-8"> -->
	<title>Update</title>
	<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.5/css/bootstrap.min.css"/>
	<style type="text/css">
		li{
			list-style: none;
			text-decoration: none;
			margin:10px;
		}
	</style>
</head>
	<body>
		<form method="POST" action="#">
			<ul>
			<li>
				<label for="cathegory">Enter 0 for view information in dat base or 1 for update data base</label>
			</li>
			<li>Enter the cathegory:</li>
				<ul>
					<li>mobile</li>
					<li>laptop</li>
					<li>others</li>
					<li>mainHeaders</li>
					<li>special</li>
				</ul>
				<li><input type="text" name="cathegory" placeholder="cathegory"></li>
				<li><input type="text" name="command" placeholder="command"></li>
				<li><button type="submit">OK</button></li>
			</ul>
		</form>
	</body>
</html>
