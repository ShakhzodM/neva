<?php
	include 'init.php';
	function addOrder($event_id, $event_date, $ticket_adult_price, $ticket_adult_quantity, $ticket_kid_price, $ticket_kid_quantity,$link){
			$barcode = generateBarcode();
			$order = [
				'event_id'=>$event_id,
				'event_date'=>$event_date,
				'ticket_adult_price'=>$ticket_adult_price,
				'ticket_adult_quantity'=>$ticket_adult_quantity,
				'ticket_kid_price'=>$ticket_kid_price,
				'ticket_kid_quantity'=>$ticket_kid_quantity,
				'barcode'=>$barcode,
			];
			$resultOfBook = sendPostData('https://api.site.com/book', $order);
			if(array_key_exists('message', $resultOfBook)){
				$resultOfAprove = sendGetData('https://api.site.com/approve', ['barcode'=>$barcode]);
				if(array_key_exists('message', $resultOfAprove)){
					 $equal_price = $ticket_adult_price * $ticket_adult_quantity + $ticket_kid_price * $ticket_kid_quantity;
					 $query = "INSERT INTO orders SET event_id=$event_id, user_id=$_SESSION[id], event_date='$event_date', ticket_adult_price=$ticket_adult_price, ticket_adult_quantity=$ticket_adult_quantity, ticket_kid_price=$ticket_kid_price, ticket_kid_quantity=$ticket_kid_quantity, equal_price=$equal_price, barcode='$barcode', created=NOW()";
					 mysqli_query($link, $query) or die(mysqli_error($link));
					return "Заказ оформлен, ваш штрих-код $barcode";
				}elseif(array_key_exists('error', $resultOfAprove)){
					return $resultOfAprove['error'];
				}
				
			}elseif(array_key_exists('error', $resultOfBook)){
				return addOrder($event_id, $event_date, $ticket_adult_price, $ticket_adult_quantity, $ticket_kid_price, $ticket_kid_quantity,$link);
			}
	}

	
	function start(){
		if(isset($_POST['event_id']) AND isset($_POST['event_date'])){
			addOrder($_POST['event_id'], $_POST['event_date'], $_POST['ticket_adult_price'], $_POST['ticket_adult_quantity'], $_POST['ticket_kid_price'], $_POST['ticket_kid_quantity'], $link);
		}
	}
	
	
	function sendGetData($url, $data){
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_URL, $url.http_build_query($data));
		$rand = rand(0,7);
		return mokeOfApprove($rand);
	}

	function generateBarcode(){
		$barcode = '';
		for($i = 0; $i < 6; $i++){
			$barcode .= rand(0,6);
		}
		return $barcode;
	}

	function sendPostData($url, $order) {
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($order));
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		$rand = rand(0,1);
		return mokeOfBook($rand);
		
	}

	function mokeOfBook($rand){
		if($rand == 0){
			$result = json_encode(['message'=>'order successfully booked']);
		}elseif($rand == 1){
			$result = json_encode(['error'=>'barcode already exists']);
		}
		$result = json_decode($result, true);
		return $result;
	}

	function mokeOfApprove($rand){
		if($rand >= 0 && $rand < 4){
			$result = json_encode(['message'=>'order successfully booked']);
		}elseif($rand == 4){
			$result = json_encode(['error'=>'event cancelled']);
		}elseif($rand == 5){
			$result = json_encode(['error'=>'no tickets']);
		}elseif($rand == 6){
			$result = json_encode(['error'=>'no seats']);
		}elseif($rand == 7){
			$result = json_encode(['error'=>'fan removed']);
		}
		$result = json_decode($result, true);

		return $result;
	}
	//var_dump(addOrder('1','1','1','1','1','1', $link));
	//start();

	
