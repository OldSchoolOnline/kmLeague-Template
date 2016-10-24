<?
define(KML_DB_HOST, 'luckdb.djspark.org');
define(KML_DB_USER, 'luckdbuser');
define(KML_DB_PASSW, 'asodiu77--uasY');
define(KML_DB_NAME, 'luckdb');
#cookie main address
define(KML_COOKIES, 'http://kmleague.pretoriangaming.com.br/');
#maximum avatar size
define(KML_AVATAR_SIZE, '20048');
#maximum avatar resolution width:height (60:60 as default)
define(KML_AVATAR_RES, '60:60');
#admin e-mail (ie. shown in mails for new registered users)
define(KML_ADMIN_MAIL, 'kmleague.pretoriangaming.com.br');
#authorization of accounts(on, off)
define(KML_ACCOUNT_AUTH, 'off');
#turn on/off registration
define(KML_REGISTRATION, 1);
#chmod which allow league script to remove files on server
define(KML_CHMOD, 0755);
#multi dirs Y-yes,N-no (if you want multi leagues be set up in different dirs set it to Y), no to display leagues list on website and switch through it
define(KML_MULTI_DIRS, 'N');
#tables prefix
define(KML_PREFIX, 'kmleague');
#default league id (set 0 if you have default page), use it just if you have KML_MULTI_LEAGUES set N
define(KML_DEFAULT_LEAGUE, 1);
#number of days to clean password change and accounts before authorization
define(KML_CLEAN_REG_PAS, 3);

?>
