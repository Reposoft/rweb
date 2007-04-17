if (!is_callable('eaccelerator_load') && !@dl('eaccelerator.so'))
{
	$_ea_uname = php_uname();
	$_ea_phpversion = trim(phpversion());
	$_ea_ostype = substr($_ea_uname,0,strpos($_ea_uname,' '));
	$_ea_iswin = strtolower(substr($_ea_uname,0,3)) == 'win' ? true : false ;
	$_ea_ext = ($_ea_iswin) ? $_ea_ext = 'dll' :  $_ea_ext = 'so' ; 		
  	$_ea_loader_file = 'eloader_'.strtolower(substr($_ea_uname,0,3)).'_'.$_ea_phpversion.'.'.$_ea_ext;
	$_ea_loader = '/loader/'.$_ea_loader_file ;	  		
  	$_ea_ext_dir= ($_ea_iswin) ? str_replace('\\','/',substr(realpath(ini_get('extension_dir')),2)) : realpath(ini_get('extension_dir'));
  	$_ea_file_dir = ($_ea_iswin) ? str_replace('\\','/',substr(dirname(__FILE__),2)) : dirname(__FILE__);	
	$_ea_fullpath=str_repeat('/..',substr_count( $_ea_ext_dir ,'/')).$_ea_file_dir.'/';		
	$_ea_i=strlen($_ea_fullpath);	
	while($_ea_i--)
	{
      $_ea_filefind=substr($_ea_fullpath,0,$_ea_i).$_ea_loader;     	  
	  if(@file_exists($_ea_filefind)) {
			if (!$_ea_iswin) { dl($_ea_filefind); }
			break;
      	}
	}
}