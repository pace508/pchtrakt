<?php
// define some constant 
define('PCHTRAKT','0.4');
define('DEBUG',(false || $_GET['debug']==1) );

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
define('SHOWPHPINFO',(false || $_GET['phpinfo']==1));
define('INI_PATH','../../pchtrakt/');
define('INI_FILE','pchtrakt.ini');
define('CSS_FILE','css/pchtrakt.css');
define('APIKEY','def6943c09e19dccb4df715bd4c9c6c74bc3b6d7');



require_once 'function.php';
require_once 'class.settings.php';



// load settings from the ini file
$conf = Settings::getInstance(INI_PATH.''.INI_FILE); 

?>
<html>
<head>
	<title><?php echo $lang['Page_Title'] ?></title>
	<style type="text/css">@import url("<?php echo CSS_FILE;?>");</style>
</head>
<body>

<form id="pchtrakt_from" name="pchtrakt_from" method="post" action="">
	<?php
	if (DEBUG)
		$conf->debug();	
		
	if ($_SERVER['REQUEST_METHOD']=='POST' && ($_POST['Submit']) ) 
	{ 
		$save = true;
		$ErrorArray = array();
		foreach ($_POST as $key => $value) { 
			
			switch ($key) {
				case 'trakt_login':
					$trakt_login=$value;
					
					if (_empty($value))
						$ErrorArray[] = $lang['Empty_Login'];
					else 
						$conf->trakt_login = $trakt_login;
					
					break;
				case 'trakt_pwd':
					$trakt_pwd=$value;
					
					if (_empty($value))
						$ErrorArray[]  = $lang['Empty_Password'];
					else 
						$conf->trakt_pwd = $trakt_pwd;
					
					break;
				case 'trakt_API':
					$trakt_API=$value;
					
					if (DEBUG && _empty($value))
						$ErrorArray[] = $lang['Empty_TraktAPI'];
					
					break;
				case 'APP_IP':
					$pch_ip=$value;
					
					if (DEBUG && _empty($value))
						$ErrorArray[] = $lang['Empty_IP'];
					else
						$conf->pch_ip = $pch_ip;
					
					break;
				case 'APP_SleepTime':
					$APP_SleepTime=$value;
					if (_empty($value))
						$ErrorArray[] = $lang['Empty_SleepTime'];	
					else
						if(!is_numeric($value))
							$ErrorArray[] = $lang['NotNumeric_SleepTime'];
						else 
							$conf->sleep_time = $value;
					
					break;
				case 'APP_RefreshTime':
					$APP_RefreshTime=$value;
					if (_empty($value))
						$ErrorArray[] = $lang['Empty_RefreshTime'];
					else
						if(!is_numeric($value))
							$ErrorArray[] = $lang['NotNumeric_RefreshTime'];
						else 			
							$conf->refresh_time = $value;		
							
					break;
				case 'APP_TVScrobble':
					$APP_TVScrobble=$value;
					$conf->enable_tvshow_scrobbling = $value;	
					
					break;
				case 'APP_FilmScrobble':
					$APP_FilmScrobble=$value;
					$conf->enable_movie_scrobbling = $value;	
					
					break;	
				case 'APP_LogFile':
					$APP_LogFile=$value;
					
					if (DEBUG && _empty($value)){
						$ErrorArray[]  = $lang['Empty_LogFile'];
					}
					else
					{
						$conf->log_file = $value;
						_checkfile(INI_PATH."".$conf->log_file,date("m.d.y H:i:s") ." => LogFile creation\n");
					}
					break;						
			}
		}	 
		
		if (DEBUG) echo $conf->debug();			
		
		if (count($ErrorArray) ==0)
		{
			if ($conf->save())
				echo "<div class='success'>".$lang['Save']."</div>";
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
  <legend><?php echo $lang['Field_Trakt']?></legend>
  <label for="trakt_login"><?php echo $lang['Login']?> :</label> 
  <input type="text" name="trakt_login" id="trakt_login" value="<?php if(isset($trakt_login)){print $trakt_login;}else{print $conf->trakt_login;} ?>" />
  <br />  <br />
  <label for="trakt_pwd"><?php echo $lang['Pwd']?> :</label>
  <input type="password" name="trakt_pwd" id="trakt_pwd" value="<?php if(isset($trakt_pwd)){print $trakt_pwd;}else{print $conf->trakt_pwd;} ?>" />
  <?php if (DEBUG) { ?>
  <br />  <br />
  <label for="trakt_API"><?php echo $lang['API_Key']?> :</label>
  <input type="text" name="trakt_API" id="trakt_API" value="<?php echo APIKEY;?>" />

  <?php } ?>  
  </fieldset> 
  <br />  <br />
  <fieldset>
 
  <legend><?php echo $lang['Field_Config']?></legend>
  <?php if (DEBUG){ ?>
  <label for="pch_ip"><?php echo $lang['API_Key']?> :</label>
  <input type="text" name="pch_ip" id="pch_ip" value="<?php  if(isset($pch_ip)){ print $pch_ip; }else{echo $conf->pch_ip;}?>" />
  <br />  <br />
  <?php } ?>
  
  <label for="APP_SleepTime"><?php echo $lang['SleepTime']?> :</label>
  <input type="text" name="APP_SleepTime" id="APP_SleepTime" value="<?php  if(isset($APP_SleepTime)){ print $APP_SleepTime; }else{echo $conf->sleep_time;}?>"/>
  <br />  <br />
  
  <label for="APP_RefreshTime"><?php echo $lang['RefreshTime']?> :</label>
  <input type="text" name="APP_RefreshTime" id="APP_RefreshTime" value="<?php  if(isset($APP_RefreshTime)){ print $APP_RefreshTime; }else{echo $conf->refresh_time;}?>"/>
  <br />  <br />
  
  <label for="APP_TVScrobble"><?php echo $lang['TV_Scrobble']?> :</label>
  <select id="APP_TVScrobble" name="APP_TVScrobble">
	<option <?php if(isset($APP_TVScrobble) && $APP_TVScrobble==1) { echo "selected";} else{if($conf->enable_tvshow_scrobbling==1){ echo "selected"; }}?> value="1"><?php echo $lang['Yes']?></option> 
	<option <?php if(isset($APP_TVScrobble) && $APP_TVScrobble==0) { echo "selected";} else{if($conf->enable_tvshow_scrobbling==0){ echo "selected"; }}?> value="0"><?php echo $lang['No']?></option> 
  </select>
  
  <br />  <br />
  
  <label for="APP_FilmScrobble"><?php echo $lang['Film_Scrobble']?> :</label>
  <select id="APP_FilmScrobble" name="APP_FilmScrobble">
  	<option <?php if(isset($APP_FilmScrobble) && $APP_FilmScrobble==1) { echo "selected";} else{if($conf->enable_movie_scrobbling==1){ echo "selected"; }}?> value="1"><?php echo $lang['Yes']?></option> 
	<option <?php if(isset($APP_FilmScrobble) && $APP_FilmScrobble==0) { echo "selected";} else{if($conf->enable_movie_scrobbling==0){ echo "selected"; }}?> value="0"><?php echo $lang['No']?></option> 
  </select>  

  <?php if (DEBUG) { ?>  
  <br />  <br />
  <label for="APP_LogFile"><?php echo $lang['LogFile']?> :</label>
  <input type="text" name="APP_LogFile" id="APP_LogFile" value="<?php  if(isset($APP_LogFile)){ print $APP_LogFile; }else{echo $conf->log_file;}?>" />
  <?php } ?>
  <br />
  
  </fieldset>

  <p style="centering">
    <input type="submit" name="Submit" value="<?php echo $lang['Submit'] ?>" class="button" />
  </p>

  <?php if (SHOWPHPINFO) phpinfo(); ?>
		
</form> 
</body>
</html>
