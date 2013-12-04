<?

 include('common.php');

 $GPIO_FILE = sprintf('/sys/class/gpio/gpio%s/value', $GPIO);

 # Common conection to MySql
 $link = mysql_connect('localhost', 'php', 'xy6Cf3oF7dBBVoWrJwiXbUc4E2CHlg9H')
  or die('Could not connect: ' . mysql_error());
 mysql_select_db('thermostat') or die('Could not select database');
 snmp_set_valueretrieval(1);

 # Begin monitoring temperature
 while (1) {
  # Get current temperature
  $temperature = snmpget('10.250.128.1', 'fly1ngm0nk3ys', '1.3.6.1.4.1.9.9.91.1.1.1.1.4.4006');
  # *** need error checking and alerting
  $temperature = sprintf("%.1f", (9/5) * $temperature  + 32);

  # Update config options from database
  $sql = "SELECT * FROM config";
  if  (!$result = mysql_query($sql)) {
   echo "Error<br>" . mysql_error($sql_select);
   die();
  }
  $titles = array();
  while ($row = mysql_fetch_array($result)) {
   $cfg[ $row['name'] ] = $row[ 'value' ];
  }

  # What is our current set point
  if (date('N') >= 1 and date('N') <= 5  &&
      date('H') >= 9 and date('H') <= 18) {
   $set = $cfg['day'];
  } else {
   $set = $cfg['night'];
  }

  if ($temperature > $set) {
   # Room temperature is warm.
   file_put_contents($GPIO_FILE, "1");
   $m = 1;
  } else {
   # Room temperature is ok.
   file_put_contents($GPIO_FILE, "0");
   $m = 0;
  }

  printf("CURRENT: %s,  CONTROL: %s,  MODE: %s\n", $temperature, $set, $MODE[ $m ]);

  sleep(3);
 }


 ?>
