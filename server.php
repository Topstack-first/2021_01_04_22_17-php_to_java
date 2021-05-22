<?php
/*
Empresa: JIS PARKING SpA.
Descripción: Sistema para la emisión de boletas electrónicas.
*/

echo '////////////////////// JIS PARKING SpA - SISTEMA DE BOLETAS //////////////////////';

set_time_limit(0);
$host = '127.0.0.1';
$port = '5500';
$socket = socket_create(AF_INET, SOCK_STREAM, getprotobyname('tcp'));
socket_bind($socket, $host, $port) or die('Error al vincular socket con la ip en ese cliente');
socket_strerror(socket_last_error());
socket_listen($socket);
$i=0;

$file_name = 'C:\xampp\htdocs\boleta\log_'. date('d-m-Y_H_i_s') .'.txt';
$file = fopen($file_name,'w');
fwrite($file, PHP_EOL ."");
fclose($file);

while(true) {
	$client[++$i] = socket_accept($socket);
	$message = socket_read($client[$i], 1024);
    $string = str_split($message, 5);

    $file = fopen($file_name,'a');
    fwrite($file, PHP_EOL ."$message");
    fclose($file);

	if($string[0] == '@**@1') {

        $response = chr(2) .'0'. chr(9) .'OK'. chr(3);
        $file = fopen($file_name,'a');
        fwrite($file, PHP_EOL ."$response");
        fclose($file);

		socket_write($client[$i], $response ."\n\r", 1024);
        $message = socket_read($client[$i], 1024);
        $string = str_split($message, 5);
        $file = fopen($file_name,'a');
        fwrite($file, PHP_EOL ."$message");
        fclose($file);
	}

	if($string[0] == '@**@2') {

        $string = str_split($message, 40);
		$detail = str_split($string[5], 1);
        $amount = '';
	
	if($detail[28] != '|')
        {
            $amount = $detail[28];
        }

        if($detail[29] != '|')
        {
            $amount = $amount.$detail[29];
        }

        if($detail[30] != '|')
        {
            $amount = $amount.$detail[30];
        }

        if($detail[31] != '|')
        {
            $amount = $amount.$detail[31];
        }

        if($detail[32] != '|')
        {
            $amount = $amount.$detail[32];
        }

        if($detail[33] != '|')
        {
            $amount = $amount.$detail[33];
        }

        if($detail[34] != '|')
        {
            $amount = $amount.$detail[34];
        }

        if($detail[35] != '|')
        {
            $amount = $amount.$detail[35];
        }

        if($detail[36] != '|')
        {
            $amount = $amount.$detail[34];
        }

        $amount = trim($amount);

        if($amount > 0)
        {
            /////////////

                $dbhost = 'localhost';
                $dbuser = 'root';
                $dbpass = '';
                $dbtable = 'electronic_bill_system';

                $mysqli = new mysqli($dbhost, $dbuser, $dbpass, $dbtable);

                //////////////////////

                $query = "SELECT folio FROM folios LIMIT 1";
                $result = $mysqli->query($query);
                $f_folio = $result->fetch_array(MYSQLI_NUM);

                $d_folios = mysqli_query($mysqli, "DELETE FROM folios WHERE folio = '".$f_folio[0]."'");

                $folio = (int)$f_folio[0];

                //////////////////////

                $query = "SELECT * FROM settings WHERE id = 1";
                $result = $mysqli->query($query);
                $f_settings = $result->fetch_array(MYSQLI_NUM);

                //////////////////////

                echo $total = $amount;

                echo $subtotal = round($total/1.19);

                echo $tax = $total - $subtotal;

                echo $date = date('Y-m-d H:i:s');

                $i_electronic_bills = $mysqli->query("INSERT INTO `electronic_bills` (`branch_office_id`, `cashier_id`, `dte_code`, `folio`, `subtotal`, `tax`, `total`, `date`) VALUES ('".$f_settings[1]."', '".$f_settings[2]."', '".$f_settings[3]."', '".$folio."', '".$subtotal."', '".$tax."', '".$total."', '". $date ."')");

                $date = explode(' ', $date);

                /////////////////////

                $ted = '<TED version="1.0"><DD><RE>76063822-6</RE><TD>39</TD><F>'.$folio.'</F><FE>'.$date[0].'</FE><RR>66666666-6</RR><RSR>N/A</RSR><MNT>'.$amount.'</MNT><IT1>PRESTACION DE ESTACIONAMIENTO.</IT1><CAF version="1.0"><DA><RE>76063822-6</RE><RS>J I S PARKING SPA</RS><TD>39</TD><FA>'.$date[0].'</FA><RSAPK><M>zmqKBYG8OvB8V8L2KHtnmHHOMTuSxDBQe9O39ll6T7HdfI30gxr5Gr/SvtYOlU+wx8TnQlqKhKILw1ckddV+gQ==</M><E>Aw==</E></RSAPK><IDK>300</IDK></DA><FRMA algoritmo="SHA1withRSA">RM+9mARL8vIvhtgSmsAZykx88Xew58vHGPAA35/JvkxjYaQeoxupSjOo/HkbP2BS4+x/b7rD7gn/Dm4abV5DEw==</FRMA></CAF><TSTED>'.$date[0].'T'.$date[1].'</TSTED></DD><FRMT algoritmo="SHA1withRSA">lNK147l1YdQTlofH/bDrxBnOX9TPdT1JoORnvvP5o9CxoKw96jmFfX4Cc9d2MQQszy7gOFXaDNo16BqZ0vd0QQ==</FRMT></TED>';

                $response = chr(2) .'0'. chr(9) .'OK'. chr(9) . $folio . chr(9) . $ted . chr(3);
                $file = fopen($file_name,'a');
                fwrite($file, PHP_EOL ."$response");
                fclose($file);
                socket_write($client[$i], $response ."\n\r", 1024);
                $message = socket_read($client[$i], 1024);
                $string = str_split($message, 5);
                $file = fopen($file_name,'a');
                fwrite($file, PHP_EOL ."$message");
                fclose($file);

            //////////
    	}
        else
        {
            $response = chr(2) .'4'. chr(9) .'Error'. chr(3);
            $file = fopen($file_name,'a');
            fwrite($file, PHP_EOL ."$response");
            fclose($file);
            socket_write($client[$i], $response ."\n\r", 1024);
            socket_close($client[$i]);
        }
    }
    else
    {
        $response = chr(2) .'0'. chr(9) .'OK'. chr(9) . '' . chr(3);
        $file = fopen($file_name,'a');
        fwrite($file, PHP_EOL ."$response");
        fclose($file);
        socket_write($client[$i], $response ."\n\r", 1024);
        $message = socket_read($client[$i], 1024);
        $string = str_split($message, 5);
        $file = fopen($file_name,'a');
        fwrite($file, PHP_EOL ."$message");
        fclose($file);
    }

	if($string[0] == '@**@3') {
		$response = chr(2) .'0'. chr(9) .'OK'. chr(3);
        $file = fopen($file_name,'a');
        fwrite($file, PHP_EOL ."$response");
        fclose($file);
		socket_write($client[$i], $response ."\n\r", 1024);
		socket_close($client[$i]);
	}
}
socket_close($client[$i]);
?>