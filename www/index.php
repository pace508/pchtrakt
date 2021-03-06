<?php
// define some constant 
define('PCHTRAKT','PHP');
define('SEC_LOW',5);
define('MIN_LOW',15);
define('DEBUG',(false || sha1($_GET['debug'])=="77e0d1c16844248a6eaacb0faa8125fc3f542580") );
define('APP_URL','https://github.com/pchtrakt/pchtrakt');
define('VIEW',strtolower(substr($_SERVER['HTTP_ACCEPT_LANGUAGE'], 0, 2)));
IF (!DEBUG)
{
	if (!file_exists('lang/'.VIEW.'.php'))
		require_once 'lang/en.php';
	else
		require_once 'lang/'.VIEW.'.php';
}
else
{
	require_once 'lang/fr.php';
}
define('SHOWPHPINFO',(false || sha1($_GET['phpinfo'])== "77e0d1c16844248a6eaacb0faa8125fc3f542580"));
define('INI_PATH','../');
define('INI_FILE','pchtrakt.ini');
define('JSON_FILE','appinfo.json');
define('CSS_FILE','css/pchtrakt.css');
define('APIKEY','def6943c09e19dccb4df715bd4c9c6c74bc3b6d7');



require_once 'function.php';
require_once 'class.settings.php';
require_once 'class.json.php';

// load settings from the ini file
$conf = Settings::getInstance(INI_PATH.''.INI_FILE);

$json = JSON::getInstance(INI_PATH.''.JSON_FILE);
?>
<html>
<head>
	<title><?php echo $lang['Page_Title'] ?></title>
	<style type="text/css">@import url("<?php echo CSS_FILE;?>");</style>
	<script type="text/javascript" src="http://code.jquery.com/jquery-1.7.1.min.js"></script>
	<script type="text/javascript">
		$(document).ready(function() {
                $('legend').click(function(){
                        $(this).parent().find('.content').slideToggle("slow");
                });
		});		
		
		<?php if ( $conf->get("BetaSeries","login") == "your_login") { ?>
			$(document).ready(function() {
				$('#BetaSeries_legend').parent().find('.content').toggle(false);
			});	
		<?php } ?>        

  </script>
</head>
<body>

<form id="pchtrakt_from" name="pchtrakt_from" method="post" action="">
	<?php
	if (DEBUG)
		$conf->debug();	
		
	if ($_SERVER['REQUEST_METHOD']=='POST' && ($_POST['Submit']) ) 
	{ 
		$Trakt_TVScrobble=0;
		$Trakt_FilmScrobble=0;
		$BetaSeries_TVScrobble=0;
		
		$conf->setValue("Trakt","enable_tvshow_scrobbling", 0);
		$conf->setValue("Trakt","enable_movie_scrobbling", 0);
		$conf->setValue("BetaSeries","enable_tvshow_scrobbling", 0);
		
		$ErrorArray = array();
		foreach ($_POST as $key => $value) { 
			switch ($key) {
				/*--------------------- BEGIN PCHTrakt Region --------------------- */	
				case 'PCHTrakt_API':
					$PCHTrakt_API=$value;
					
					if (DEBUG && _empty($value))
						$ErrorArray[] = $lang['PCHTrakt_Empty_API'];
					
					break;
				case 'PCHTrakt_IP':
					$PCHTrakt_IP=$value;
					
					if (DEBUG && _empty($value))
						$ErrorArray[] = $lang['PCHTrakt_Empty_IP'];
					else
						$conf->setValue("PCHtrakt","pch_ip", $value); 
					
					break;
				case 'PCHTrakt_SleepTime':
					$PCHTrakt_SleepTime=$value;
					if (_empty($value))
						$ErrorArray[] = $lang['PCHTrakt_Empty_SleepTime'];	
					else
						if(!is_numeric($value) || $value < SEC_LOW)
							$ErrorArray[] = $lang['PCHTrakt_NotNumeric_SleepTime'];
						else 
							$conf->setValue("PCHtrakt","sleep_time", $value);
					
					break;
				case 'PCHTrakt_LogFile':
					$PCHTrakt_LogFile=$value;
					
					if (DEBUG && _empty($value)){
						$ErrorArray[]  = $lang['PCHTrakt_Empty_LogFile'];
					}
					else
					{
						$conf->setValue("PCHtrakt","log_file", $value);
						_checkfile(INI_PATH."".$conf->log_file,'');
					}
					break;					
				/*--------------------- END PCHTrakt Region ------------------------ */	

				
				/*--------------------- BEGIN Trakt Region --------------------- */	
				case 'Trakt_Login':
					$Trakt_Login=$value;
					
					if (_empty($value))
						$ErrorArray[] = $lang['Trakt_Empty_Login'];
					else 
						$conf->setValue("Trakt","login", $Trakt_Login); 
					
					break;
				case 'Trakt_Password':
					$Trakt_Password=$value;
					
					if (_empty($value))
						$ErrorArray[]  = $lang['Trakt_Empty_Password'];
					else 
						$conf->setValue("Trakt","password", $Trakt_Password); 
					
					break;

				case 'Trakt_RefreshTime':
					$Trakt_RefreshTime=$value;
					if (_empty($value))
						$ErrorArray[] = $lang['Trakt_Empty_RefreshTime'];
					else
						if(!is_numeric($value) || $value < MIN_LOW)
							$ErrorArray[] = $lang['Trakt_NotNumeric_RefreshTime'];
						else 			
							$conf->setValue("Trakt","refresh_time", $value);	
							
					break;
				case 'Trakt_TVScrobble':
					$Trakt_TVScrobble=1;					
					$conf->setValue("Trakt","enable_tvshow_scrobbling",  1);
					break;
					
				case 'Trakt_FilmScrobble':
					$Trakt_FilmScrobble=1;			
					$conf->setValue("Trakt","enable_movie_scrobbling", 1);
					break;	
				/*--------------------- END Trakt Region ----------------------- */
				
				
				/*--------------------- BEGIN BetaSeries Region --------------------- */	
				case 'BetaSeries_Login' :
					$BetaSeries_Login=$value;
					
					if (_empty($value))
						$ErrorArray[] = $lang['BetaSeries_Empty_Login'];
					else 
						$conf->setValue("BetaSeries","login", $BetaSeries_Login); 
									
					break;
				case 'BetaSeries_Password':
					$BetaSeries_Password=$value;
					
					if (_empty($value))
						$ErrorArray[] = $lang['BetaSeries_Empty_Password'];
					else 
						$conf->setValue("BetaSeries","password", $BetaSeries_Password); 						
					break;
					
				case 'BetaSeries_TVScrobble':
					$BetaSeries_TVScrobble=1;		
					$conf->setValue("BetaSeries","enable_tvshow_scrobbling", 1);		
					break;
				/*--------------------- END BetaSeries Region --------------------- */		
			}
		}	 
		
		if (DEBUG) echo $conf->debug();			
		
		if (count($ErrorArray) ==0)
		{
			if ($conf->save())
			{
		?>	
		<script type="text/javascript">	
		<?php if ( $conf->get("BetaSeries","login") == "your_login") { ?>
			$(document).ready(function() {
				$('#BetaSeries_legend').parent().find('.content').toggle(false);
			});	
		<?php }
			else {?>
			$(document).ready(function() {
				$('#BetaSeries_legend').parent().find('.content').toggle(true);
			});	
		<?php }?>      

  </script>
			<?php
				if (_checkAuth()==false)
					echo "<div class='warning'>".$lang['Trakt_Failed']."</div>";
				else
					echo "<div class='success'>".$lang['Save']."</div>";
			}
			else
				echo "<div class='error'>".$lang['Error']."</div>";		
		}
		else
		{ 
			echo '<div class="warning"><ul>';
			foreach($ErrorArray as $error)
				echo '<li>'.$error.'</li>';

			echo '</ul></div>';
		}
	} 
?>
	
  <fieldset>
  <legend><?php echo $json->name;?></legend>
	<label for="pchtrakt_version">Version : <a target="blank" href="<?php echo APP_URL ?>"><?php echo  $json->version; ?></a> </label>  
  </fieldset> 

<?php if (DEBUG){ ?>
	<fieldset>
 
		<legend><?php echo $lang['PCHTrakt_Config']?></legend>

		<label for="pch_ip"><?php echo $lang['PCHTrakt_IP']?> :</label>
		<input type="text" name="pch_ip" id="pch_ip" value="<?php  if(isset($pch_ip)){ print $pch_ip; }else{echo $conf->get("PCHtrakt","pch_ip");}?>" />
		<br />  <br />

	  
		<label for="PCHTrakt_SleepTime"><?php echo $lang['PCHTrakt_SleepTime']?> (<?php echo $lang['sec'] ?>) :</label>
		<input type="text" name="PCHTrakt_SleepTime" id="PCHTrakt_SleepTime" value="<?php  if(isset($PCHTrakt_SleepTime)){ print $PCHTrakt_SleepTime; }else{print $conf->get("PCHtrakt","sleep_time");}?>"/>
	 

		<br />  <br />
		<label for="PCHTrakt_LogFile"><?php echo $lang['PCHTrakt_LogFile']?> :</label>
		<input type="text" name="PCHTrakt_LogFile" id="PCHTrakt_LogFile" value="<?php  if(isset($PCHTrakt_LogFile)){ print $PCHTrakt_LogFile; }else{print $conf->get("PCHtrakt","log_file");}?>" />

	  

		<br />  <br />
		<label for="PCHTrakt_API"><?php echo $lang['PCHTrakt_API']?> :</label>
		<input type="text" name="PCHTrakt_API" id="PCHTrakt_API" value="<?php echo APIKEY;?>" />

	</fieldset>
<?php } ?>  
  
  
 <fieldset>
  <legend id="Trakt_legend"><?php echo $lang['Trakt_Config']?></legend>
  <div class="content">
  <label for="Trakt_Login"><?php echo $lang['Login']?> :</label> 
  <input type="text" name="Trakt_Login" id="Trakt_Login" value="<?php if(isset($Trakt_Login)){print $Trakt_Login;}else{print $conf->get("Trakt","login");} ?>" />
  <br />  <br />
  <label for="Trakt_Password"><?php echo $lang['Pwd']?> :</label>
  <input type="password" name="Trakt_Password" id="Trakt_Password" value="<?php if(isset($Trakt_Password)){print $Trakt_Password;}else{print $conf->get("Trakt","password");} ?>" />
   <br />  <br />
  <label for="Trakt_RefreshTime"><?php echo $lang['Trakt_RefreshTime']?> (<?php echo $lang['min'] ?>) :</label>
  <input type="text" name="Trakt_RefreshTime" id="Trakt_RefreshTime" value="<?php  if(isset($Trakt_RefreshTime)){ print $Trakt_RefreshTime; }else{print $conf->get("Trakt","refresh_time");}?>"/>
  <br />  <br />
  
  <label for="Trakt_TVScrobble"><?php echo $lang['TV_Scrobble']?> :</label>

  <input type="checkbox" name="Trakt_TVScrobble[]" value=1 <?php if(isset($Trakt_TVScrobble) && _bool($Trakt_TVScrobble)==1) {  echo "checked";} else{if( _bool($conf->get("Trakt","enable_tvshow_scrobbling"),true)==1){ echo "checked"; }}?> >
  
  
  <br />  <br />
  
  <label for="Trakt_FilmScrobble"><?php echo $lang['Film_Scrobble']?> :</label>
  <input type="checkbox" name="Trakt_FilmScrobble[]" value=1 <?php if(isset($Trakt_FilmScrobble) && _bool($Trakt_FilmScrobble)==1) {  echo "checked";} else{if( _bool($conf->get("Trakt","enable_movie_scrobbling"),true)==1){ echo "checked"; }}?> >
  
</div>
  </fieldset>   
 

 
  <fieldset>
  <legend id="BetaSeries_legend"><?php echo $lang['BetaSeries_Config']?></legend>
    <div class="content">
  <label for="BetaSeries_Login"><?php echo $lang['Login']?> :</label> 
  <input type="text" name="BetaSeries_Login" id="BetaSeries_Login" value="<?php if(isset($BetaSeries_Login)){print $BetaSeries_Login;}else{print $conf->get("BetaSeries","login");} ?>" />
  <br />  <br />
  <label for="BetaSeries_Password"><?php echo $lang['Pwd']?> :</label>
  <input type="password" name="BetaSeries_Password" id="BetaSeries_Password" value="<?php if(isset($BetaSeries_Password)){print $BetaSeries_Password;}else{print $conf->get("BetaSeries","password");} ?>" />
   <br />  <br />
  
  <label for="BetaSeries_TVScrobble"><?php echo $lang['TV_Scrobble']?> :</label>
  <input type="checkbox" name="BetaSeries_TVScrobble[]" value=1 <?php if(isset($BetaSeries_TVScrobble) && _bool($BetaSeries_TVScrobble)==1) {  echo "checked";} else{if( _bool($conf->get("BetaSeries","enable_tvshow_scrobbling"),true)==1){ echo "checked"; }}?> >
  
  </div>
  </fieldset>   
  
  <p style="centering">
    <input type="submit" name="Submit" value="<?php echo $lang['Submit'] ?>" class="button" />
  </p>

  <?php if (SHOWPHPINFO) phpinfo(); ?>
		
</form> 
</body>
</html>
