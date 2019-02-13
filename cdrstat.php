<?php
error_reporting(1);
set_time_limit(0);
  
if(!($sock = socket_create(AF_INET, SOCK_STREAM, 0)))
{
    $errorcode = socket_last_error();
    $errormsg = socket_strerror($errorcode);
     
    die("Couldn't create socket: [$errorcode] $errormsg \r\n");
}
 
echo "Socket created \r\n";
if (is_dir("./logdata")) {
echo ("The dir [./logdata] exists.\r\n");
} else {
mkdir("./logdata", 0755);
echo("The dir [./logdata] created - OK!\r\n");
}

 
// Bind the source address
if( !socket_bind($sock, "10.244.6.7" , 9000) )
{
    $errorcode = socket_last_error();
    $errormsg = socket_strerror($errorcode);
     
    die("Could not bind socket : [$errorcode] $errormsg \r\n");
}
 
echo "Socket bind - OK! \r\n";
 
if(!socket_listen ($sock , 10))
{
    $errorcode = socket_last_error();
    $errormsg = socket_strerror($errorcode);
     
    die("Could not listen on socket : [$errorcode] $errormsg \r\n");
}
 
echo "Socket listen - OK! \r\n";
 
echo "Waiting for incoming connections... \r\n";
 
//Accept incoming connection - This is a blocking call
$client =  socket_accept($sock);
 
//display information about the client who is connected
if(socket_getpeername($client , $address , $port))
{
    echo "Client $address : $port is now connected to us. \r\n";
}
 while (true)
{
//read data from the incoming socket
$input = socket_read($client, 1024);

$text = preg_replace('|[\s]+|s', ' ', $input);		
$gooddata = explode(" ", $text);
$datesectime = date("H:i:s", mktime(0, 0, $gooddata[2]));

$date1 = str_split($gooddata[0],2);
$date2 = str_split($gooddata[1],2);

$date11 = date("d.m.Y", mktime(0, 0, 0, $date1[1], $date1[0], $date1[2]));
$date22 = date("H:i", mktime($date2[0], $date2[1], 0, 0, 0, 0)); 

//$opis = "$gooddata[0]".';' ."$gooddata[1]". ';' ."$datesectime". ';' ."$gooddata[3]". ';' ."$gooddata[4]". ';' ."$gooddata[5]". ';' ."$gooddata[6]". ';' ."$gooddata[7]". ';' ."$gooddata[8]". ';' ."$gooddata[9]";
$opis = "$date11".';' ."$date22". ';' ."$datesectime". ';' ."$gooddata[6]". ';' ."$gooddata[7]";
$winfo2file = "".$opis."\r\n";

if (isset ($gooddata[6], $gooddata[7]) AND strlen($gooddata[6]) >= 4 OR strlen($gooddata[7]) >= 4){ //проверка на валидность
//Запись звонков на мобильные в отдельный файл дублироване
if (strlen($gooddata[6]) > 7 OR strlen($gooddata[7]) > 7) {
$fp = fopen("./logdata/mobile.csv", "a");
fwrite($fp, $winfo2file);
fclose($fp);
echo "Mobile detected! ".$gooddata[6].". \r\n";
}
//проверка по номеру	
if(strlen($gooddata[6]) == 4){
if (file_exists('./logdata/'.$gooddata[6].'.csv')) {
$fps = fopen('./logdata/'.$gooddata[6].'.csv', 'a');
fwrite($fps, $winfo2file);
fclose($fps);

} else {
$first_line = "Дата". '; ' ."Время". '; ' ."Время разговора". '; ' ."Куда звонил". '; ' ."Кто звонил";
$fps = fopen('./logdata/'.$gooddata[6].'.csv', 'a');
fwrite($fps,$first_line."\r\n\r\n");
fwrite($fps, $winfo2file);
fclose($fps);
echo "Detected! File created - OK! [".$gooddata[6]."]\r\n";
}}
else {
if (file_exists('./logdata/'.$gooddata[7].'.csv')) {
$fps = fopen('./logdata/'.$gooddata[7].'.csv', 'a');
fwrite($fps, $winfo2file);
fclose($fps);
} 
else {
$first_line = "Дата". '; ' ."Время". '; ' ."Время разговора". '; ' ."Куда звонил". '; ' ."Кто звонил";
$fps = fopen('./logdata/'.$gooddata[7].'.csv', 'a');
fwrite($fps,$first_line."\r\n\r\n");
fwrite($fps, $winfo2file);
fclose($fps);
echo "Detected! File created - OK! [".$gooddata[7]."]\r\n";
}}
//запись всех логов 
$fp = fopen("./logdata/allstats.csv", "a");
fwrite($fp, $winfo2file);
fclose($fp);
echo "[".$winfo2file."]\r\n";

$dbisert = "INSERT INTO cdrstat (timeday, timehour, talktime, whom, who) VALUES ('".$date11."', '".$date22."', '".$datesectime."', '".$gooddata[6]."', '".$gooddata[7]."');\r\n";
$fopen = fopen("./logdata/mysqldata.sql", "a");
fwrite($fopen, $dbisert);
fclose($fopen);


}}

